<?php

namespace App\Form;

use App\Entity\Appointment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use App\Entity\Pet;

class AppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('petName', null, [
                'label' => "Pet's Name",
                'required' => false,
            ]);
        $builder
            // Appointment Information
            ->add('dateTime', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Appointment Date & Time',
            ])
            ->add('location', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Location',
                'required' => false,
                'choices' => [
                    'Main Clinic' => 'Main Clinic',
                    'Branch A' => 'Branch A',
                    'Branch B' => 'Branch B',
                ],
                'placeholder' => 'Select a location',
            ])
            ->add('reason', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => "Primary Reason for Today's Visit",
                'required' => false,
                'choices' => [
                    'Wellness Exam' => 'Wellness Exam',
                    'Sick Visit' => 'Sick Visit',
                    'Vaccination' => 'Vaccination',
                    'Recheck' => 'Recheck',
                    'Other' => 'Other',
                ],
                'placeholder' => 'Select a reason',
            ])
            // Owner/Client Information
            ->add('ownerName', null, [
                'label' => 'Full Name(s) of Owner(s)',
                'required' => false,
            ])
            ->add('contactPhone', null, [
                'label' => 'Best Contact Phone Number',
                'required' => false,
            ])
            ->add('address', null, [
                'label' => 'Physical Address',
                'required' => false,
            ])
            ->add('emergencyContact', null, [
                'label' => 'Emergency Contact Information',
                'required' => false,
            ])
            // Pet Details
            ->add('petSpecies', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => "Pet's Species",
                'required' => false,
                'choices' => [
                    'Dog' => 'Dog',
                    'Cat' => 'Cat',
                    'Rabbit' => 'Rabbit',
                    'Other' => 'Other',
                ],
                'placeholder' => 'Select species',
            ])
            ->add('petBreed', null, [
                'label' => "Pet's Breed",
                'required' => false,
            ])
            ->add('petColor', null, [
                'label' => "Pet's Color",
                'required' => false,
            ])
            ->add('petDob', DateTimeType::class, [
                'widget' => 'single_text',
                'label' => 'Pet Age or Date of Birth',
                'required' => false,
            ])
            ->add('petSex', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Pet Sex',
                'required' => false,
                'choices' => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                ],
                'placeholder' => 'Select sex',
            ])
            ->add('petSpayNeuter', \Symfony\Component\Form\Extension\Core\Type\ChoiceType::class, [
                'label' => 'Spay/Neuter Status',
                'required' => false,
                'choices' => [
                    'Spayed' => 'Spayed',
                    'Neutered' => 'Neutered',
                    'Intact' => 'Intact',
                ],
                'placeholder' => 'Select status',
            ])
            ->add('petWeight', null, [
                'label' => 'Pet Weight',
                'required' => false,
            ])
            ->add('petMicrochip', null, [
                'label' => 'Microchip Number',
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Appointment::class,
        ]);
    }
}
