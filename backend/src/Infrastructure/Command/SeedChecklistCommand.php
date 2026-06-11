<?php

declare(strict_types=1);

namespace App\Infrastructure\Command;

use App\Domain\Candidate\Enum\ApplicationType;
use App\Domain\Candidate\Enum\DocumentType;
use App\Entity\ChecklistItem;
use App\Entity\ChecklistTemplate;
use App\Repository\ChecklistTemplateRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:seed:checklist', description: 'Seed checklist templates for applications')]
final class SeedChecklistCommand extends Command
{
    public function __construct(
        private ChecklistTemplateRepository $templateRepository,
        private EntityManagerInterface $entityManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $this->seedCampusFrance();
        $this->entityManager->flush();

        $io->success('Checklists seed terminé.');

        return Command::SUCCESS;
    }

    private function seedCampusFrance(): void
    {
        $template = $this->templateRepository->findOneBy(['code' => ApplicationType::CAMPUS_FRANCE->value]);
        if (!$template) {
            $template = new ChecklistTemplate(ApplicationType::CAMPUS_FRANCE->value, 'Campus France — Dossier standard', ApplicationType::CAMPUS_FRANCE);
            $this->entityManager->persist($template);
        }

        $items = [
            [DocumentType::PASSPORT, 'Passeport', 1, true],
            [DocumentType::CV, 'CV', 2, true],
            [DocumentType::LANGUAGE_CERTIFICATE, 'Certificat de langue (TCF/DELF)', 3, true],
            [DocumentType::MOTIVATION_LETTER, 'Lettre de motivation', 4, true],
            [DocumentType::DIPLOMA, 'Diplôme', 5, false],
            [DocumentType::TRANSCRIPT, 'Relevés de notes', 6, false],
        ];

        $existing = [];
        foreach ($template->getItems() as $item) {
            $existing[$item->getDocumentType()->value] = $item;
        }

        foreach ($items as [$type, $label, $order, $required]) {
            if (isset($existing[$type->value])) {
                continue;
            }
            $template->addItem(new ChecklistItem($type, $label, $order, $required));
        }
    }
}
