<?php

declare(strict_types=1);

namespace App\Handler;

use App\Service\DiscordService;
use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;

/**
 * Monolog Handler to log into a discord channel.
 */
class DiscordMonologHandler extends AbstractProcessingHandler
{
    /** DiscordMonologHandler constructor. */
    public function __construct(protected DiscordService $discordService)
    {
        parent::__construct(Level::Critical, true);
    }

    /** @throws \Exception */
    public function write(LogRecord $record): void
    {
        $this->discordService->log(
            sprintf('%s: `%s`', Logger::toMonologLevel($record->level), $record->message)
        );
    }
}
