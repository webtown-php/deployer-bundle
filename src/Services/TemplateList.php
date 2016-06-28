<?php
/**
 * Created by PhpStorm.
 * User: whitezo
 * Date: 2016. 06. 27.
 * Time: 12:09
 */

namespace WebtownPhp\DeployerBundle\Services;


use WebtownPhp\DeployerBundle\Deployer\DeployerTemplateInterface;

class TemplateList
{
    private $templates;

    public function __construct()
    {
        $this->templates = array();
    }

    public function push(DeployerTemplateInterface $transport)
    {
        $this->templates[] = $transport;
    }

    public function getTemplates()
    {
        return $this->templates;
    }
}
