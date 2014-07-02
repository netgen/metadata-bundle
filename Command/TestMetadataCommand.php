<?php

namespace Netgen\MetadataBundle\Command;

use eZ\Publish\Core\Base\Exceptions\ContentFieldValidationException;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMetadataCommand extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id
     */
    protected function configure()
    {
        $this->setName( 'ezpublish:test:metadata' );
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface  $input  An InputInterface instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output An OutputInterface instance
     *
     * @return null|integer null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     * @see    setCode()
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = $this->getContainer()->get( "ezpublish.api.repository" );
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $fieldTypeService = $repository->getFieldTypeService();

        $contentId = 2;

        try
        {
            $content = $contentService->loadContent( $contentId );


            $contentType = $contentTypeService->loadContentType( $content->contentInfo->contentTypeId );
            // iterate over the field definitions of the content type and print out each field's identifier and value


            foreach( $contentType->fieldDefinitions as $fieldDefinition )
            {
                $output->write( $fieldDefinition->identifier . ": " );
                $fieldType = $fieldTypeService->getFieldType( $fieldDefinition->fieldTypeIdentifier );
                $field = $content->getFieldValue( $fieldDefinition->identifier );

                // We use the Field's toHash() method to get readable content out of the Field
                $valueHash = $fieldType->toHash( $field );
                $output->writeln( $field );
            }
        }
        catch( \eZ\Publish\API\Repository\Exceptions\NotFoundException $e )
        {
            // if the id is not found
            $output->writeln( "No content with id $contentId" );
        }
        catch( \eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e )
        {
            // not allowed to read this content
            $output->writeln( "Anonymous users are not allowed to read content with id $contentId" );
        }
    }
}
