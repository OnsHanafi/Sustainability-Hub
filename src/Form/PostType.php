<?php

namespace App\Form;

use App\Entity\Post;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title',TextareaType::class, array('label' => ' ','attr' => array( 'placeholder' => 'type your post title','class' => 'form-control col-xs-4','style'=>'width:100%;')))
            ->add('details',TextareaType::class, array('label' => ' ','attr' => array( 'placeholder' => 'type your post details','class' => 'form-control col-xs-4','style'=>'width:100%;')))

        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Post::class,
        ]);
    }
}
