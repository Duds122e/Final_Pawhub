<?php

namespace App\Command;

use App\Entity\Pet;
use App\Repository\PetRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:seed-adoption-pets',
    description: 'Create sample pets with status "available" for the adoption catalog',
)]
final class SeedAdoptionPetsCommand extends Command
{
    private const SAMPLES = [
        ['name' => 'Buddy', 'type' => 'Dog', 'breed' => 'Golden Retriever', 'age' => 2],
        ['name' => 'Luna', 'type' => 'Cat', 'breed' => 'Domestic Shorthair', 'age' => 1],
        ['name' => 'Milo', 'type' => 'Dog', 'breed' => 'Beagle', 'age' => 3],
    ];

    public function __construct(
        private readonly PetRepository $pets,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('force', null, InputOption::VALUE_NONE, 'Add samples even if adoptable pets already exist');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $force = (bool) $input->getOption('force');

        $availableCount = 0;
        foreach ($this->pets->findAll() as $pet) {
            if (strtolower((string) $pet->getStatus()) === 'available') {
                ++$availableCount;
            }
        }

        if ($availableCount > 0 && !$force) {
            $io->success(sprintf('Skipping seed: %d adoptable pet(s) already exist.', $availableCount));

            return Command::SUCCESS;
        }

        $created = 0;
        foreach (self::SAMPLES as $sample) {
            $pet = new Pet();
            $pet->setName($sample['name']);
            $pet->setType($sample['type']);
            $pet->setBreed($sample['breed']);
            $pet->setAge($sample['age']);
            $pet->setStatus('available');
            $this->em->persist($pet);
            ++$created;
        }

        $this->em->flush();
        $io->success(sprintf('Created %d adoptable pet(s).', $created));

        return Command::SUCCESS;
    }
}
