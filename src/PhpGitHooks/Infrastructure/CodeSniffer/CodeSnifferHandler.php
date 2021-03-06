<?php

namespace PhpGitHooks\Infrastructure\CodeSniffer;

use PhpGitHooks\Application\Message\MessageConfigData;
use PhpGitHooks\Command\BadJobLogo;
use PhpGitHooks\Infrastructure\Common\ToolHandler;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

/**
 * Class CodeSnifferHandler.
 */
class CodeSnifferHandler extends ToolHandler
{
    /** @var string */
    private $neddle;
    /** @var string */
    private $standard = 'PSR2';

    /**
     * @param array $messages
     *
     * @throws InvalidCodingStandardException
     */
    public function run(array $messages)
    {
        // start and displays the progress bar
        $this->progress->start();
        $this->progress->setMessage('Checking '.$this->standard.' code style with PHPCS');
        $this->output->write($this->outputHandler->getTitle());

        foreach ($this->files as $index => $file) {
            $this->progress->advance();
            if (!preg_match($this->neddle, $file)) {
                continue;
            }

            $processBuilder = new ProcessBuilder(array('php', 'bin/phpcs', '--standard='.$this->standard, $file));
            /** @var Process $phpCs */
            $phpCs = $processBuilder->getProcess();
            $phpCs->run();

            if (false === $phpCs->isSuccessful()) {
                $this->outputHandler->setError($phpCs->getOutput());
                $this->output->writeln($this->outputHandler->getError());
                $this->output->writeln(BadJobLogo::paint($messages[MessageConfigData::KEY_ERROR_MESSAGE]));
                $this->output->writeln('Break at '.$file);

                throw new InvalidCodingStandardException();
            }
        }

        $this->progress->finish();

        $this->output->writeln($this->outputHandler->getSuccessfulStepMessage());
    }

    /**
     * @param string $neddle
     */
    public function setNeddle($neddle)
    {
        $this->neddle = $neddle;
    }

    /**
     * @param array $standard
     */
    public function setStandard($standard)
    {
        $this->standard = $standard;
    }
}
