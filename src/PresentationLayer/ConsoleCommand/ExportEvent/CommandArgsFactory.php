<?php

declare(strict_types=1);

namespace App\PresentationLayer\ConsoleCommand\ExportEvent;

use Symfony\Component\Console\Input\InputInterface;

class CommandArgsFactory
{
    public function __construct()
    {
    }

    public function __invoke(InputInterface $input): object
    {
        return $this->create($input);
    }

    public function create(InputInterface $input): object
    {
        return (object)
        [
            'date' => $input->getOption('date'),
            'type' => $input->getOption('type'),
            'destinationTable' => $input->getOption('destination-table'),
            'shouldRemoveSourceFile' => (bool) $input->getOption('remove-source'),
        ];
    }
}
