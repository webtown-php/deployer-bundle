<?php

namespace WebtownPhp\DeployerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class RollbackDbCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('webtown:deployer:rollback-db')
            ->setDescription('Rollback DB');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Starting rollback');
        $projectRoot = realpath($this->getContainer()->get('kernel')->getRootDir().'/..');

        $prevRoot = $this->getPrevRoot($projectRoot);

        // migrations
        $currentMigrations = $this->getMigrations($projectRoot);
        $prevMigrations    = $this->getMigrations(dirname($projectRoot).'/'.$prevRoot);

        // get differences in reverse order
        $diff = array_reverse(array_diff($currentMigrations, $prevMigrations));

        // run each execute command
        $command = $this->getApplication()->find('doctrine:migrations:execute');
        foreach ($diff as $item) {
            $output->writeln('Rolling back: '.$item);
            $arguments = array(
                'command' => 'doctrine:migrations:execute',
                'version' => $item,
                '--down'  => true,
            );
            $in = new ArrayInput($arguments);
            if ($input->hasParameterOption(array('--no-interaction', '-n'))) {
                $in->setInteractive(false);
            }
            $command->run($in, $output);
        }
    }

    /**
     * @param $projectRoot
     *
     * @return array
     */
    protected function getMigrations($projectRoot)
    {
        $path = sprintf('%s/app/DoctrineMigrations', $projectRoot);

        $ret = [];

        $finder = new Finder();
        foreach ($finder->files()->in($path)->depth('== 0')->sortByName() as $item) {
            $ret[] = preg_replace('/[^\d]+/', '', pathinfo($item->getRelativePathname(), PATHINFO_FILENAME));
        }

        return $ret;
    }

    /**
     * Return penultimate version dir name
     *
     * @param $currentPath
     *
     * @return string
     */
    protected function getPrevRoot($currentPath)
    {
        $up = dirname($currentPath);

        $finder = new Finder();
        $list = [];
        foreach ($finder->directories()->in($up)->depth('== 0')->sortByName() as $item) {
            $list[] = $item->getRelativePathname();
        }

        // the last is the current version
        array_pop($list);

        // return the prev. version
        return array_pop($list);
    }
}
