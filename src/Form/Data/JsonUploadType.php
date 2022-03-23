<?php

namespace App\Form\Data;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotNull;

class JsonUploadType extends AbstractType
{
  public function buildForm(FormBuilderInterface $builder, array $options)
  {
    $builder
        ->add('json', FileType::class, [
            'label'       => 'data.json-file',
            'constraints' => [
                new NotNull(),
                new File([
                    'mimeTypes' => ['application/json', 'text/plain'],
                ]),
            ],
        ])
        ->add('submit', SubmitType::class);
  }
}
