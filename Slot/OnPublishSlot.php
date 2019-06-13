<?php

namespace SQLI\PublishSchedulerBundle\Slot;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use SQLI\PublishSchedulerBundle\Services\Handlers\PublishSchedulerHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class OnPublishSlot extends BaseSlot
{
    /** @var \eZ\Publish\API\Repository\ContentService */
    private $contentService;
    /** @var ContainerInterface */
    private $container;

    public function __construct( ContentService $contentService, ContainerInterface $container )
    {
        $this->contentService = $contentService;
        $this->container      = $container;
    }

    public function receive( Signal $signal )
    {
        if( !$signal instanceof Signal\ContentService\PublishVersionSignal )
        {
            return;
        }

        // Get handler for "publish"
        $schedulerHandlerName = $this->container->getParameter( 'sqli_publish_scheduler.publish_scheduler_handler' );
        $schedulerHandlerName = str_replace( '@', '', $schedulerHandlerName );

        /** @var PublishSchedulerHandlerInterface $schedulerHandler */
        $schedulerHandler = $this->container->get( $schedulerHandlerName );

        // Load published content
        $content = $this->contentService->loadContent( $signal->contentId, null, $signal->versionNo );

        // Forward to "publish" handler
        $schedulerHandler->onPublishSlot( $content );
    }
}