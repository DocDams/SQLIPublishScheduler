<?php

namespace SQLI\PublishSchedulerBundle\Services\Handlers;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;

interface PublishSchedulerHandlerInterface
{
    /**
     * Find all contents that should be "published"
     *
     * @param \DateTime|null $currentDatetime
     * @return array[Location]
     */
    public function searchPublishPending( \DateTime $currentDatetime = null );

    /**
     * Find all contents that should be "unpublished"
     *
     * @param \DateTime|null $currentDatetime
     * @return array[Location]
     */
    public function searchUnpublishPending( \DateTime $currentDatetime = null );

    /**
     * Unhide location to "publish" content
     *
     * @param Location $location
     */
    public function publish( Location $location );

    /**
     * Hide location to "unpublish" content
     *
     * @param Location $location
     */
    public function unpublish( Location $location );

    /**
     * Actions to perform when a content is published
     *
     * @param Content $content
     */
    public function onPublishSlot( Content $content );
}