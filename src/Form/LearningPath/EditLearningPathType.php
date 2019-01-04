<?php

namespace App\Form\LearningPath;

use App\Entity\LearningPath;
use App\Form\Type\SaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditLearningPathType extends AbstractType
{
  /**
   * @param FormBuilderInterface $builder
   * @param array                $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
        ->add('name', TextType::class, [
            'label' => 'learning-path.name',
        ])
        ->add('question', TextareaType::class, [
            'label' => 'learning-path.question',
        ])
        ->add('submit', SaveType::class, [
            'list_route'           => 'app_learningpath_list',
            'enable_cancel'        => true,
            'enable_save_and_list' => false,
            'cancel_label'         => 'form.discard',
            'cancel_route'         => 'app_learningpath_list',
        ]);
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver
        ->setDefault('data_class', LearningPath::class);
  }
}