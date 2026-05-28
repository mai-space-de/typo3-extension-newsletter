<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Command;

use Maispace\MaiNewsletter\Domain\Model\Campaign;
use Maispace\MaiNewsletter\Domain\Repository\CampaignRepository;
use Maispace\MaiNewsletter\Service\CampaignDispatcher;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'newsletter:dispatch',
    description: 'Dispatch all scheduled newsletter campaigns that are due.',
)]
final class DispatchNewsletterCommand extends Command
{
    public function __construct(
        private readonly CampaignRepository $campaignRepository,
        private readonly CampaignDispatcher $campaignDispatcher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'campaign-uid',
            null,
            InputOption::VALUE_REQUIRED,
            'Dispatch a single campaign by UID instead of all due campaigns.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $campaignUid = $input->getOption('campaign-uid');

        if ($campaignUid !== null) {
            $campaign = $this->campaignRepository->findByUid((int) $campaignUid);
            if ($campaign === null) {
                $io->error(sprintf('Campaign with UID %d not found.', (int) $campaignUid));
                return Command::FAILURE;
            }
            $campaigns = [$campaign];
        } else {
            $campaigns = $this->campaignRepository->findDue(new \DateTimeImmutable())->toArray();
        }

        if ($campaigns === []) {
            $io->success('No campaigns due for dispatch.');
            return Command::SUCCESS;
        }

        $errors = 0;

        foreach ($campaigns as $campaign) {
            try {
                $count = $this->campaignDispatcher->dispatch($campaign);
                $io->success(sprintf(
                    'Campaign "%s" dispatched to %d subscriber(s).',
                    $campaign->getTitle(),
                    $count,
                ));
            } catch (\InvalidArgumentException $e) {
                $io->warning(sprintf('Skipped campaign "%s": %s', $campaign->getTitle(), $e->getMessage()));
                $errors++;
            }
        }

        return $errors > 0 ? Command::FAILURE : Command::SUCCESS;
    }
}
