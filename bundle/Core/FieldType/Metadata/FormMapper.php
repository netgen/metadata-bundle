<?php

namespace Netgen\Bundle\MetadataBundle\Core\FieldType\Metadata;

use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\FieldType\FieldValueFormMapperInterface;
use Netgen\Bundle\MetadataBundle\Form\Type\FieldType\MetadataFieldType;
use Symfony\Component\Form\FormInterface;

class FormMapper implements FieldValueFormMapperInterface
{
    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldForm
            ->add(
                $fieldForm->getConfig()->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        MetadataFieldType::class,
                        array(
                            'required' => $data->fieldDefinition->isRequired,
                            'label' => $data->fieldDefinition->getName(),
                        )
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }
}
