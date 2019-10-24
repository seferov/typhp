<?php

namespace Seferov\Typhp\Command;

use Jean85\PrettyVersions;
use PhpParser\Error;
use Seferov\Typhp\Analyser;
use Seferov\Typhp\Configuration;
use Seferov\Typhp\Issue\IssueInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Yaml\Exception\ParseException;

class AnalyseCommand extends Command
{
    private const EXIT_CODE_INVALID_CONFIGURATION = 1;
    private const EXIT_CODE_SYNTAX_ERROR = 2;
    private const EXIT_CODE_HAS_ISSUES = 4;

    protected static $defaultName = 'analyse';

    protected function configure(): void
    {
        $this
            ->setAliases(['analyze'])
            ->addArgument('path', InputArgument::OPTIONAL, 'Path to analyse. Ignores config file if provided.')
            ->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'config file', getcwd().'/.typhp.yml')
            ->addOption('format', null, InputOption::VALUE_REQUIRED, 'format can be either table or compact', 'table')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(sprintf('<info>Typhp %s</info>', PrettyVersions::getVersion('seferov/typhp')->getPrettyVersion()));

        $stopwatch = new Stopwatch();
        $stopwatch->start($this->getName());

        $configuration = new Configuration($input->getOption('config'), $input->getArgument('path'));

        $exitCode = 0;
        $totalIssueCount = 0;

        try {
            $files = $configuration->getFiles();
        } catch (ParseException $e) {
            $output->writeln('<error>Config file is misconfigured</error>');

            return self::EXIT_CODE_INVALID_CONFIGURATION;
        }

        foreach ($files as $file) {
            $analyser = new Analyser($file->getPathname(), $file->getContents());
            try {
                $issueCollection = $analyser->analyse();
            } catch (Error $e) {
                $output->writeln(sprintf('<error>Could\'t parse %s</error> %s', $file->getPathname(), $e->getMessage()));
                $exitCode = $exitCode | self::EXIT_CODE_SYNTAX_ERROR;
                continue;
            }

            $issueCount = $issueCollection->count();
            if (!$issueCount) {
                continue;
            }

            $totalIssueCount += $issueCount;
            $output->writeln('');
            $output->writeln(sprintf('<info>File: %s</info> has %d issues', $file->getPathname(), $issueCount));

            switch ($input->getOption('format')) {
                case 'table':
                    $table = new Table($output);
                    $table->setHeaders(['Line', 'Name', 'Issue']);

                    /** @var IssueInterface $issue */
                    foreach ($issueCollection as $issue) {
                        $table->addRow([$issue->getLine(), $issue->getName(), $issue->getIssue()]);
                    }
                    $table->render();
                    break;
                case 'compact':
                    /** @var IssueInterface $issue */
                    foreach ($issueCollection as $issue) {
                        $output->writeln($issue->getIssueCompact());
                    }
                    break;
                default:
                    $output->writeln(sprintf('<error>%s is not a valid format choice</error>', $input->getOption('format')));

                    return $exitCode | self::EXIT_CODE_INVALID_CONFIGURATION;
            }
        }

        $stopwatchEvent = $stopwatch->stop($this->getName());
        $output->writeln(sprintf('Memory: %.2F MB, Time: %d ms', $stopwatchEvent->getMemory() / 1024000, $stopwatchEvent->getDuration()));

        if ($totalIssueCount > 0) {
            $output->writeln(sprintf('<error>%d issues found!</error>', $totalIssueCount));
            $exitCode = $exitCode | self::EXIT_CODE_HAS_ISSUES;
        }

        if (0 === $exitCode) {
            $output->writeln('<fg=white;bg=green>No issue found!</>');
        }

        return $exitCode;
    }
}
