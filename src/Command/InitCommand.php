<?php

namespace WebtownPhp\DeployerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class InitCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('webtown:deployer:init')
            ->setDescription('Generate Kunstmaan deployer.php')
            ->addOption(
                'template',
                null,
                InputOption::VALUE_REQUIRED,
                'Template name'
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        /** @var string $templateSource template generator */
        $templateSource = $input->getOption('template');
        /** @var array $templateSources template sources */
        $templateSources = [];
        foreach ($this->getContainer()->get('deployer.templates')->getTemplates() as $key => $taggedService) {
            $templateSources[$taggedService->getName()] = $taggedService;
        }

        if (is_null($templateSource) || ! isset($templateSources[$templateSource])) {
            $templateSource = $io->choice('Template source', array_keys($templateSources));
        }

        if (!$templateSource) {
            throw new \RuntimeException('No template source available');
        }

        $templateSource = $templateSources[$templateSource];

        $questionHelper = new QuestionHelper();
        $templateSource->generateTemplateContent($questionHelper, $input, $output);
    }
}
