<?php

namespace App\Form\Contributor;

use App\Entity\Contributor;
use App\Entity\StudyArea;
use App\Form\Type\SaveType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EditContributorType extends AbstractType
{
  /**
   * @param FormBuilderInterface $builder
   * @param array                $options
   */
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
        ->add('name', TextType::class, [
            'label' => 'contributor.name',
        ])
        ->add('description', TextType::class, [
            'empty_data' => '',
            'label'      => 'contributor.description',
            'required'   => false,
        ])
        ->add('url', UrlType::class, [
            'label'    => 'contributor.url',
            'required' => false,
        ])
        ->add('submit', SaveType::class, [
            'enable_cancel'        => true,
            'enable_save_and_list' => false,
            'cancel_label'         => 'form.discard',
            'cancel_route'         => 'app_contributor_list',
        ]);
  }

  /**
   * @param OptionsResolver $resolver
   */
  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver
        ->setRequired('studyArea')
        ->setAllowedTypes('studyArea', StudyArea::class)
        ->setDefault('data_class', Contributor::class);
  }
}
