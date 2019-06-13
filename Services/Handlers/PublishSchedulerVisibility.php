<?php

namespace SQLI\PublishSchedulerBundle\Services\Handlers;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationQuery;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;
use eZ\Publish\Core\FieldType\DateAndTime\Value as DateAndTimeValue;

class PublishSchedulerVisibility implements PublishSchedulerHandlerInterface
{
    /** @var string */
    protected $fieldnamePublish;
    /** @var string */
    protected $fieldnameUnpublish;
    /** @var SearchService */
    protected $searchService;
    /** @var Repository */
    protected $repository;

    public function __construct( string $fieldnamePublish, string $fieldnameUnpublish, Repository $repository )
    {
        $this->fieldnamePublish   = $fieldnamePublish;
        $this->fieldnameUnpublish = $fieldnameUnpublish;
        $this->repository         = $repository;
        $this->searchService      = $repository->getSearchService();
    }

    /**
     * Find all contents that should be "published"
     *
     * @param \DateTime|null $currentDatetime
     * @return array[Location]
     */
    public function searchPublishPending( \DateTime $currentDatetime = null )
    {
        $results = [];

        try
        {
            if( is_null( $currentDatetime ) )
            {
                $currentDatetime = new \DateTime();
            }

            // Contents hidden and with publish_date field in the past (and not null)
            $criterion = new Criterion\LogicalAnd( [
                                                       new Criterion\Visibility( Criterion\Visibility::HIDDEN ),
                                                       new Criterion\LogicalNot( new Criterion\Field( $this->fieldnamePublish, Criterion\Operator::LIKE, null ) ),
                                                       new Criterion\Field( $this->fieldnamePublish, Criterion\Operator::LTE, $currentDatetime->getTimestamp() ),
                                                   ] );

            $searchQuery               = new LocationQuery();
            $searchQuery->limit        = 0;
            $searchQuery->query        = $criterion;
            $searchQuery->performCount = true;

            // Count contents that need to be processed (will be used to determine LIMIT clause)
            $countResults = $this->searchService->findLocations( $searchQuery, [], false );

            if( $countResults->totalCount > 0 )
            {
                $searchQuery->limit = $countResults->totalCount;
                // Find all contents
                $searchResults = $this->searchService->findLocations( $searchQuery, [], false );

                foreach( $searchResults->searchHits as $searchHit )
                {
                    // Construct result array
                    $results[] = $searchHit->valueObject;
                }
            }
        }
        catch( \Exception $exception )
        {
            // TODO : Log error
        }

        return $results;
    }

    /**
     * Find all contents that should be "unpublished"
     *
     * @param \DateTime|null $currentDatetime
     * @return array[Location]
     */
    public function searchUnpublishPending( \DateTime $currentDatetime = null )
    {
        $results = [];

        try
        {
            if( is_null( $currentDatetime ) )
            {
                $currentDatetime = new \DateTime();
            }

            // Contents hidden and with unpublish_date field in the past (and not null)
            $criterion = new Criterion\LogicalAnd( [
                                                       new Criterion\Visibility( Criterion\Visibility::VISIBLE ),
                                                       new Criterion\LogicalNot( new Criterion\Field( $this->fieldnameUnpublish, Criterion\Operator::LIKE, null ) ),
                                                       new Criterion\Field( $this->fieldnameUnpublish, Criterion\Operator::LTE, $currentDatetime->getTimestamp() ),
                                                   ] );

            $searchQuery               = new LocationQuery();
            $searchQuery->limit        = 0;
            $searchQuery->query        = $criterion;
            $searchQuery->performCount = true;

            // Count contents that need to be processed (will be used to determine LIMIT clause)
            $countResults = $this->searchService->findLocations( $searchQuery, [], false );

            if( $countResults->totalCount > 0 )
            {
                $searchQuery->limit = $countResults->totalCount;
                // Find all contents
                $searchResults = $this->searchService->findLocations( $searchQuery, [], false );

                foreach( $searchResults->searchHits as $searchHit )
                {
                    // Construct result array
                    $results[] = $searchHit->valueObject;
                }
            }
        }
        catch( \Exception $exception )
        {
            // TODO : Log error
        }

        return $results;
    }

    /**
     * Unhide location to "publish" content
     *
     * @param Location $location
     */
    public function publish( Location $location )
    {
        // Force unhide
        $this->repository->sudo(
            function( Repository $repository ) use ( $location )
            {
                return $repository->getLocationService()->unhideLocation( $location );
            }
        );
    }

    /**
     * Hide location to "unpublish" content
     *
     * @param Location $location
     */
    public function unpublish( Location $location )
    {
        // Force hide
        $this->repository->sudo(
            function( Repository $repository ) use ( $location )
            {
                return $repository->getLocationService()->hideLocation( $location );
            }
        );
    }

    /**
     * Actions to perform when a content is published
     *
     * @param Content $content
     * @throws \eZ\Publish\API\Repository\Exceptions\BadStateException
     * @throws \eZ\Publish\API\Repository\Exceptions\UnauthorizedException
     */
    public function onPublishSlot( Content $content )
    {
        // Check publish_date field
        $publishFieldValue = $content->getFieldValue( $this->fieldnamePublish );

        if( $publishFieldValue instanceof DateAndTimeValue )
        {
            $publishDatetime = $publishFieldValue->value;

            // If no publish_date, don't hide locations
            if( !is_null( $publishDatetime ) )
            {
                // If a publish_date is defined then check that's not reached
                $currentDatetime = new \DateTime();
                if( $currentDatetime < $publishDatetime )
                {
                    // Publish date not reached then hide all locations
                    $locationList = $this->repository->getLocationService()->loadLocations( $content->contentInfo );
                    foreach( $locationList as $location )
                    {
                        $this->repository->getLocationService()->hideLocation( $location );
                    }
                }
            }
        }
    }
}