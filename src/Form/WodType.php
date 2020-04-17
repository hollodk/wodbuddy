<?php

namespace App\Form;

use App\Entity\Wod;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class WodType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('startAt')
            ->add('name')
            ->add('scoringType', ChoiceType::class, [
                'choices' => [
                    '' => 'How to enter score',
                    'Time' => 'time',
                    'Rounds and reps' => 'rounds-reps',
                    'Reps' => 'reps',
                    'Load' => 'load',
                    'Calories' => 'calories',
                    'Points' => 'points',
                    'Meters' => 'meters',
                ],
            ])
            ->add('stream')
            ->add('timer', ChoiceType::class, [
                'choices' => [
                    'Clock' => 'clock',
                    'Timer' => 'timer',
                    'Tabata' => 'tabata',
                    'EMOM' => 'emom',
                ],
            ])
            ->add('description', null, [
                'attr' => [
                    'rows' => 10,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Wod::class,
        ]);
    }
}
