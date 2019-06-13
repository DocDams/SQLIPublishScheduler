<?php

namespace SQLI\PublishSchedulerBundle\Command;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use SQLI\PublishSchedulerBundle\Services\Handlers\PublishSchedulerVisibility;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class PublishUnpublishCommand extends ContainerAwareCommand
{
    /** @var int */
    private $pid;
    /** @var PublishSchedulerVisibility */
    private $publishScheduler;

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    public function initialize( InputInterface $input, OutputInterface $output )
    {
        $output->setDecorated( true );
        $this->pid                   = getmypid();
        $publishSchedulerServiceName = $this->getContainer()->getParameter( 'sqli_publish_scheduler.publish_scheduler_handler' );
        $publishSchedulerServiceName = str_replace( "@", "", $publishSchedulerServiceName );
        $this->publishScheduler      = $this->getContainer()->get( $publishSchedulerServiceName );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute( InputInterface $input, OutputInterface $output )
    {
        $output->writeln( "<info>Job started ! PID : {$this->pid}</info>" );
        $output->writeln( "" );

        // Get locations that need to be published
        $locations = $this->publishScheduler->searchPublishPending();

        // Processing publish each location
        foreach( $locations as $location )
        {
            /** @var Location $location */
            $output->writeln( "Publish : ". $location->getContent()->getName() );
            $this->publishScheduler->publish( $location );
        }

        // Get locations that need to be unpublished
        $locations = $this->publishScheduler->searchUnpublishPending();

        // Processing unpublish each location
        foreach( $locations as $location )
        {
            /** @var Location $location */
            $output->writeln( "Unpublish : ". $location->getContent()->getName() );
            $this->publishScheduler->unpublish( $location );
        }

        $output->writeln( "" );
        $output->writeln( "<info>Job finished ! PID : {$this->pid}</info>" );
    }

    protected function configure()
    {
        $this->setName( 'sqli:publish_scheduler' )
            ->setDescription( 'Publish/unpublish command' );
    }
}