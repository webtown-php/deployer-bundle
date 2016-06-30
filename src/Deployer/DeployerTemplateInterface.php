<?php
/**
 * Created by PhpStorm.
 * User: whitezo
 * Date: 2016. 06. 24.
 * Time: 15:40
 */

namespace WebtownPhp\DeployerBundle\Deployer;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Template generator inteface
 */
interface DeployerTemplateInterface
{
    /**
     * @param QuestionHelper $questionHelper
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function generateTemplateContent(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output);

    /**
     * @return string
     */
    public function getName();
}
