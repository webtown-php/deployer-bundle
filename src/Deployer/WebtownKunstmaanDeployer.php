<?php
/**
 * Created by PhpStorm.
 * User: whitezo
 * Date: 2016. 06. 24.
 * Time: 15:44
 */

namespace WebtownPhp\DeployerBundle\Deployer;


use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Yaml\Yaml;
use WebtownPhp\DeployerBundle\Exception\ServerAddException;

class WebtownKunstmaanDeployer implements DeployerTemplateInterface
{
    /**
     * @var InputInterface
     */
    protected $input;
    /**
     * @var OutputInterface
     */
    protected $output;
    /**
     * @var SymfonyStyle
     */
    protected $io;
    /**
     * @var QuestionHelper
     */
    protected $questionHelper;
    /**
     * @var \Twig_Environment
     */
    protected $twig;
    /**
     * @var string
     */
    protected $rootDir;
    /**
     * @var \AppKernel
     */
    private $kernel;

    /**
     * TestDeployer constructor.
     * @param \Twig_Environment $twig
     * @param \AppKernel $kernel
     */
    public function __construct(\Twig_Environment $twig, \AppKernel $kernel)
    {
        $this->twig = $twig;
        $this->kernel = $kernel;
        $this->rootDir = realpath($this->kernel->getRootDir().'/..');
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'webtown.kunstmaan';
    }

    /**
     * @param QuestionHelper $questionHelper
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    public function generateTemplateContent(QuestionHelper $questionHelper, InputInterface $input, OutputInterface $output)
    {
        $this->questionHelper = $questionHelper;
        $this->input = $input;
        $this->output = $output;
        $this->io = new SymfonyStyle($input, $output);

        // get params
        $params = $this->getParams();
        // deploy.php gen
        $this->generateDeployPhp($params);
        
        // optional: servers.yml
        if ($params['generate_servers_yml']) {
            $this->generateServersYml($params['servers']);
        }
    }

    /**
     * @param array $params
     */
    protected function generateDeployPhp(array $params)
    {
        $ret = $this->twig->render('@WebtownPhpDeployer/default/deploy.php.twig', $params);
        $path = $this->getRootDir().'/deploy.php';
        file_put_contents($path, $ret);
    }

    /**
     * Create servers.yml
     *
     * @param array $list
     */
    protected function generateServersYml(array $list)
    {
        foreach ($list as &$item) {
            unset($item['name']);
            if ('' === $item['password']) {
                unset($item['password']);
                $item['identity_file'] = '~';
            }
        }

        file_put_contents($p = sprintf('%s/app/config/servers.yml', $this->getRootDir()), Yaml::dump($list));
    }

    /**
     * Ask for deployer params
     *
     * @return array
     */
    protected function getParams()
    {
        $ret = [];

        // ask url
        $ret['repository'] = $this->io->ask('Repository URL', null, function ($url) {

            if (1 !== preg_match('#((git|ssh|http(s)?)|(git@[\w\.]+))(:(//)?)([\w\.@\:/\-~]+)(\.git)(/)?#', $url)) {
                throw new \RuntimeException('Invalid repository URL');
            }

            return $url;
        });

        // ask source
        $ret['generate_servers_yml'] = $this->io->confirm('Generate servers.yml', false);

        // request servers
        $servers = [];
        $this->io->text('Please provide deployment server(s), and finish with an empty value');
        $l = true;
        while ($l) {
            try {
                $server = [];
                $server['name'] = $this->ask('Name');
                $server['host'] = $this->ask('Host');
                $server['port'] = $this->ask('Port', '22');
                $server['user'] = $this->ask('User');

                $question = $this->getQuestion('Password (leave empty for key auth.)', '');
                $server['password'] = $this->getQuestionHelper()->ask($this->input, $this->output, $question);

                $server['stage'] = $this->ask('Stage');
                $server['deploy_path'] = $this->ask('Deploy path');
                $servers[ $server['name'] ] = $server;
            } catch (ServerAddException $e) {
                $l = false;
            }
        }

        $ret['servers'] = $servers;

        return $ret;
    }

    /**
     * Ask inline question
     *
     * @param string $question
     * @param callable|null $validator
     * @return string
     * @throws ServerAddException
     */
    protected function ask($question, $default = '', callable $validator = null)
    {
        $question = $this->getQuestion($question, $default);
        $question->setValidator($validator);
        $ret = $this->getQuestionHelper()->ask($this->input, $this->output, $question);

        // break input sequence if value is empty
        if (empty($ret)) {
            throw new ServerAddException();
        }

        return $ret;
    }

    /**
     * @return QuestionHelper
     */
    protected function getQuestionHelper()
    {
        return $this->questionHelper;
    }

    /**
     * @return string
     */
    protected function getRootDir()
    {
        return $this->rootDir;
    }

    /**
     * @param $question
     * @param $default
     * @param string $sep
     *
     * @return Question
     */
    public function getQuestion($question, $default, $sep = ':')
    {
        return new Question($default ?
            sprintf('<info>%s</info> [<comment>%s</comment>]%s ', $question, $default, $sep) :
            sprintf('<info>%s</info>%s ', $question, $sep));
    }
}
