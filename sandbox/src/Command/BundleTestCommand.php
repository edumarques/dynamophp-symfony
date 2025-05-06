<?php

declare(strict_types=1);

namespace Sandbox\Command;

use EduardoMarques\DynamoPHP\ODM\EntityManager;
use EduardoMarques\DynamoPHP\ODM\EntityManagerException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;

#[AsCommand(name: 'sandbox:bundle-test')]
final class BundleTestCommand extends Command
{
    public function __construct(
        private readonly EntityManager $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->entityManager->describe(\stdClass::class);
        } catch (EntityManagerException) {
            //
        } catch (Throwable $exception) {
            $io->error(sprintf('Bundle set up failed. Error: %s', $exception->getMessage()));
            return Command::FAILURE;
        }

        $io->success('Bundle successfully set up.');

        return Command::SUCCESS;
    }
}
