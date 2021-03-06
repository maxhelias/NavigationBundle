<?php

namespace DH\NavigationBundle\Command;

use DateTime;
use DH\NavigationBundle\Model\Routing\Summary;
use DH\NavigationBundle\NavigationManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class RoutingCommand extends Command
{
    protected static $defaultName = 'navigation:routing';

    /**
     * @var NavigationManager
     */
    private $manager;

    public function __construct(NavigationManager $manager)
    {
        $this->manager = $manager;

        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setName('navigation:routing')
            ->setDescription('Computes a route')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED)
            ->addOption('waypoint', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Point de passage')
            ->addOption('departure', null, InputOption::VALUE_REQUIRED, 'Departure date and time (YYYY-MM-DDD HH:II:SS)')
            ->addOption('arrival', null, InputOption::VALUE_REQUIRED, 'Arrival date and time (YYYY-MM-DDD HH:II:SS)')
            ->addOption('traffic', null, InputOption::VALUE_REQUIRED, 'Traffic mode (enabled/disabled/default depending on provider)')
            ->addOption('language', null, InputOption::VALUE_REQUIRED, 'Language (fr-FR, en-US, etc.)')
            ->setHelp(
                <<<'EOF'
The <info>navigation:routing</info> command will compute a route from the given addresses.

You can choose a provider with the "provider" option.

<info>php bin/console navigation:routing --waypoint="45.834278,1.260816" --waypoint="44.830109,-0.603649" --provider=here</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (\count($input->getOption('waypoint')) < 2) {
            throw new InvalidArgumentException('A route needs at least two waypoints (start and end locations).');
        }

        if ($input->getOption('provider')) {
            $this->manager->using($input->getOption('provider'));
        }

        $query = $this->manager->createRoutingQuery();

        foreach ($input->getOption('waypoint') as $waypoint) {
            $query->addWaypoint($waypoint);
        }

        if ($input->getOption('departure')) {
            $query->setDepartureTime(new DateTime($input->getOption('departure')));
        }

        if ($input->getOption('arrival')) {
            $query->setArrivalTime(new DateTime($input->getOption('arrival')));
        }

        if ($input->getOption('traffic')) {
            $query->setTrafficMode($input->getOption('traffic'));
        }

        if ($input->getOption('language')) {
            $query->setLanguage($input->getOption('language'));
        }

        $response = $query->execute();

        $io = new SymfonyStyle($input, $output);

        $routes = $response->getRoutes();

        $io->section('Summary');

        $data = [];
        foreach ($routes as $index => $route) {
            /**
             * @var Summary
             */
            $summary = $route->getSummary();
            $data[] = [
                $index + 1,
                $summary->getDistance()->getFormattedValue(2),
                $summary->getTravelTime()->getFormattedValue(2),
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['route', 'distance', 'duration'])
            ->setRows($data)
        ;
        $table->render();

        $io->newLine();

        foreach ($routes as $routeIndex => $route) {
            $io->section('Route #'.($routeIndex + 1));

            $legs = $route->getLegs();
            foreach ($legs as $legIndex => $leg) {
                $steps = $leg->getSteps();
                $io->writeln(sprintf('<comment>Leg #%d</comment> - %d steps.', $legIndex + 1, \count($steps)));

                $data = [];
                foreach ($steps as $stepIndex => $step) {
                    $data[] = [
                        $stepIndex + 1,
                        implode(', ', $step->getPosition()),
                        $step->getDistance()->getFormattedValue(2),
                        $step->getDuration()->getFormattedValue(2),
                        $step->getInstruction(),
                    ];
                }

                $table = new Table($output);
                $table
                    ->setHeaders(['step', 'position', 'distance', 'duration', 'instruction'])
                    ->setRows($data)
                    ->setColumnMaxWidth(4, 100)
                ;
                $table->render();
                $io->newLine();
            }
        }

        return 0;
    }
}
