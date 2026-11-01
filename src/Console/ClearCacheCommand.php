<?php

declare(strict_types=1);

namespace Qiick\Excubia\Console;

use Qiick\Excubia\Configuration\ExcubiaConfigurationLoader;
use Qiick\Excubia\Exceptions\InvalidConfigurationException;
use Qiick\Excubia\Services\ResponseCacheService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * `excubia cache:clear`. Deletes cached data-source responses only.
 */
final class ClearCacheCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('cache:clear')
            ->setDescription('Delete cached data-source responses.')
            ->addArgument('path', InputArgument::OPTIONAL, 'Project directory')
            ->addOption('config', null, InputOption::VALUE_REQUIRED, 'Config file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $path = $input->getArgument('path');
            $projectRoot = is_string($path) && $path !== '' ? $path : $this->workingDirectory();
            $config = $input->getOption('config');
            $configuration = (new ExcubiaConfigurationLoader)->load(is_string($config) ? $config : null, $projectRoot);
            (new ResponseCacheService($configuration->cacheDirectory, $configuration->cacheTtlHours, false, false))->clear();
            $output->writeln('Cache cleared.');

            return 0;
        } catch (InvalidConfigurationException $exception) {
            $this->writeError($output, $exception->getMessage());

            return $exception->getCode();
        }
    }

    private function writeError(OutputInterface $output, string $message): void
    {
        if ($output instanceof ConsoleOutputInterface) {
            $output->getErrorOutput()->writeln($message);

            return;
        }

        $output->writeln($message);
    }

    private function workingDirectory(): string
    {
        $cwd = getcwd();

        if ($cwd === false) {
            throw new InvalidConfigurationException('The working directory could not be read.');
        }

        return $cwd;
    }
}
