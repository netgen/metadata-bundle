<?php

namespace Netgen\Bundle\MetadataBundle\Form\Type\FieldType;

use eZ\Publish\SPI\FieldType\FieldType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class MetadataFieldType extends AbstractType
{
    /**
     * @var \eZ\Publish\SPI\FieldType\FieldType
     */
    private $fieldType;

    public function __construct(FieldType $fieldType)
    {
        $this->fieldType = $fieldType;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'title',
                TextType::class,
                array(
                    'label' => 'content.field_type.xrowmetadata.title',
                )
            )
            ->add(
                'description',
                TextareaType::class,
                array(
                    'label' => 'content.field_type.xrowmetadata.description',
                )
            )
            ->add(
                'keywords',
                TextType::class,
                array(
                    'label' => 'content.field_type.xrowmetadata.keywords',
                )
            )
            ->add(
                'priority',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'content.field_type.xrowmetadata.priority.automatic' => '',
                        '0.0' => '0.0',
                        '0.1' => '0.1',
                        '0.2' => '0.2',
                        '0.3' => '0.3',
                        '0.4' => '0.4',
                        '0.5' => '0.5',
                        '0.6' => '0.6',
                        '0.7' => '0.7',
                        '0.8' => '0.8',
                        '0.9' => '0.9',
                        '1.0' => '1.0',
                    ),
                    'choices_as_values' => true,
                    'label' => 'content.field_type.xrowmetadata.priority',
                )
            )
            ->add(
                'change',
                ChoiceType::class,
                array(
                    'choices' => array(
                        'content.field_type.xrowmetadata.change.always' => 'always',
                        'content.field_type.xrowmetadata.change.hourly' => 'hourly',
                        'content.field_type.xrowmetadata.change.daily' => 'daily',
                        'content.field_type.xrowmetadata.change.weekly' => 'weekly',
                        'content.field_type.xrowmetadata.change.monthly' => 'monthly',
                        'content.field_type.xrowmetadata.change.yearly' => 'yearly',
                        'content.field_type.xrowmetadata.change.never' => 'never',
                    ),
                    'choices_as_values' => true,
                    'label' => 'content.field_type.xrowmetadata.change',
                )
            )
            ->add(
                'sitemap_use',
                CheckboxType::class,
                array(
                    'label' => 'content.field_type.xrowmetadata.sitemap_use',
                )
            )
            ->addModelTransformer(new FieldValueTransformer($this->fieldType));
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_xrowmetadata';
    }
}
