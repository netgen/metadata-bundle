<?php

namespace Netgen\Bundle\MetadataBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestMetadataCommand // extends ContainerAwareCommand
{
    /**
     * This method override configures on input argument for the content id.
     */
    protected function configure()
    {
        $this->setName('netgen:metadata:test');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface  $input  An InputInterface instance
     * @param \Symfony\Component\Console\Output\OutputInterface $output An OutputInterface instance
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @throws \LogicException When this abstract method is not implemented
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var \eZ\Publish\API\Repository\Repository $repository */
        $repository = $this->getContainer()->get('ezpublish.api.repository');
        $contentService = $repository->getContentService();
        $contentTypeService = $repository->getContentTypeService();
        $fieldTypeService = $repository->getFieldTypeService();

        $contentId = 38684;

        // fetch content and display xrowmetadata field value

        try {
            $content = $contentService->loadContent($contentId);

            $contentType = $contentTypeService->loadContentType($content->contentInfo->contentTypeId);
            // iterate over the field definitions of the content type and print out each field's identifier and value

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->fieldTypeIdentifier == 'xrowmetadata') {
                    $output->write($fieldDefinition->fieldTypeIdentifier . ': ');
                    $fieldType = $fieldTypeService->getFieldType($fieldDefinition->fieldTypeIdentifier);
                    $field = $content->getFieldValue($fieldDefinition->identifier);

                    // We use the Field's toHash() method to get readable content out of the Field
                    $valueHash = $fieldType->toHash($field);
                    $output->writeln($valueHash['xml']);
                }
            }
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            // if the id is not found
            $output->writeln("No content with id $contentId");
        } catch (\eZ\Publish\API\Repository\Exceptions\UnauthorizedException $e) {
            // not allowed to read this content
            $output->writeln("Anonymous users are not allowed to read content with id $contentId");
        }

        // update current data
        $userService = $repository->getUserService();
        $user = $userService->loadUser(14);

        $repository->setCurrentUser($user);

        try {
            $contentInfo = $contentService->loadContentInfo($contentId);
            $contentDraft = $contentService->createContentDraft($contentInfo);

            $contentUpdateStruct = $contentService->newContentUpdateStruct();

            $metadata = array(
            'title' => 'Test title',
            'keywords' => array('keyword1', 'keyword2'),
            'sitemap_use' => true,
            'priority' => 0.3,
            'change' => 'monthly',
        );

            $contentUpdateStruct->setField('metadata', $metadata, 'ger-DE');

            $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
            $content = $contentService->publishVersion($contentDraft->versionInfo);
        } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
            // react on content not found
            $output->writeln($e->getMessage());
        } catch (\eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException $e) {
            // react on a field is not valid
            $output->writeln($e->getMessage());
        } catch (\eZ\Publish\API\Repository\Exceptions\ContentValidationException $e) {
            // react on a required field is missing or empty
            $output->writeln($e->getMessage());
        }
    }
}
