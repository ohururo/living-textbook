<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrintedTextType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $accessMethod = $options['access_method'];

    $builder->addViewTransformer(new CallbackTransformer(function ($dataValue) use ($accessMethod) {
      if ($dataValue != null && $accessMethod) {
        $dataValue = $dataValue->$accessMethod();
      }

      return $dataValue;
    }, function ($viewValue) {
      // This transformation is not required
    }));
  }

  public function buildView(FormView $view, FormInterface $form, array $options)
  {
    $view->vars['text_only'] = $options['text_only'];
  }

  public function configureOptions(OptionsResolver $resolver)
  {
    $resolver
        ->setDefaults([
            'access_method' => null,
            'disabled'      => true,
            'required'      => false,
            'text_only'     => false,
        ])
        ->setAllowedTypes('text_only', 'bool');
  }

  public function getParent()
  {
    return TextType::class;
  }
}
