<?php

declare(strict_types=1);

namespace Netgen\Bundle\MetadataBundle\Command;

use eZ\Publish\API\Repository\Exceptions\ContentFieldValidationException;
use eZ\Publish\API\Repository\Exceptions\ContentValidationException;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\UnauthorizedException;
use eZ\Publish\API\Repository\Repository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class TestMetadataCommand extends Command
{
    /**
     * @var \eZ\Publish\API\Repository\Repository
     */
    private $repository;

    public function __construct(Repository $repository)
    {
        $this->repository = $repository;

        // Parent constructor call is mandatory for commands registered as services
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('netgen:metadata:test');
    }

    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $contentService = $this->repository->getContentService();
        $contentTypeService = $this->repository->getContentTypeService();
        $fieldTypeService = $this->repository->getFieldTypeService();

        $contentId = 38684;

        // fetch content and display xrowmetadata field value

        try {
            $content = $contentService->loadContent($contentId);

            $contentType = $contentTypeService->loadContentType($content->contentInfo->contentTypeId);
            // iterate over the field definitions of the content type and print out each field's identifier and value

            foreach ($contentType->fieldDefinitions as $fieldDefinition) {
                if ($fieldDefinition->fieldTypeIdentifier === 'xrowmetadata') {
                    $output->write($fieldDefinition->fieldTypeIdentifier . ': ');
                    $fieldType = $fieldTypeService->getFieldType($fieldDefinition->fieldTypeIdentifier);
                    $field = $content->getFieldValue($fieldDefinition->identifier);

                    // We use the Field's toHash() method to get readable content out of the Field
                    $valueHash = $fieldType->toHash($field);
                    $output->writeln($valueHash['xml']);
                }
            }
        } catch (NotFoundException $e) {
            // if the id is not found
            $output->writeln("No content with id {$contentId}");
        } catch (UnauthorizedException $e) {
            // not allowed to read this content
            $output->writeln("Anonymous users are not allowed to read content with id {$contentId}");
        }

        // update current data
        $userService = $this->repository->getUserService();
        $user = $userService->loadUser(14);

        $this->repository->getPermissionResolver()->setCurrentUserReference($user);

        try {
            $contentInfo = $contentService->loadContentInfo($contentId);
            $contentDraft = $contentService->createContentDraft($contentInfo);

            $contentUpdateStruct = $contentService->newContentUpdateStruct();

            $metadata = [
                'title' => 'Test title',
                'keywords' => ['keyword1', 'keyword2'],
                'sitemap_use' => true,
                'priority' => 0.3,
                'change' => 'monthly',
            ];

            $contentUpdateStruct->setField('metadata', $metadata, 'ger-DE');

            $contentDraft = $contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
            $contentService->publishVersion($contentDraft->versionInfo);
        } catch (NotFoundException $e) {
            // react on content not found
            $output->writeln($e->getMessage());

            return 1;
        } catch (ContentFieldValidationException $e) {
            // react on a field is not valid
            $output->writeln($e->getMessage());

            return 1;
        } catch (ContentValidationException $e) {
            // react on a required field is missing or empty
            $output->writeln($e->getMessage());

            return 1;
        }

        return 0;
    }
}
