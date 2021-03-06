<?php

/*
 * The MIT License (MIT)
 *
 * Copyright (c) 2016
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

namespace JamesHalsall\Licenser\Command;

use JamesHalsall\Licenser\Factory\LicenseHeaderFactory;
use JamesHalsall\Licenser\Licenser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * AbstractLicenserCommand
 *
 * @package JamesHalsall\Licenser\Command
 * @author  James Halsall <james@rippleffect.com>
 */
abstract class AbstractLicenserCommand extends Command
{
    /**
     * The licenser utility
     *
     * @var Licenser
     */
    protected $licenser;

    /**
     * The license header factory.
     *
     * @var LicenseHeaderFactory
     */
    protected $licenseHeaderFactory;

    /**
     * Constructor
     *
     * @param Licenser             $licenser             The licenser utility
     * @param LicenseHeaderFactory $licenseHeaderFactory The license header factory
     */
    public function __construct(Licenser $licenser, LicenseHeaderFactory $licenseHeaderFactory)
    {
        $this->licenser = $licenser;
        $this->licenseHeaderFactory = $licenseHeaderFactory;

        parent::__construct();
    }

    /**
     * Configures the base command options
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'sources',
                InputArgument::REQUIRED,
                'The path to the source files that the licenser will process'
            )
            ->addArgument(
                'license',
                InputArgument::REQUIRED,
                'The name of a built in license or a path to the file containing your custom license header doc block ' .
                'as it will appear when prepended to your source files'
            )
            ->addOption(
                'owners',
                'o',
                InputOption::VALUE_OPTIONAL,
                'The owner email addresses of the licensed files. This is used in conjunction with the built-in ' .
                'license to add the email address(es) of the license(es) to the license header. Can be a comma ' .
                'separated list of email addresses or a single email address'
            )
        ;
    }

    /**
     * Initializes the Licenser instance with license information
     *
     * @param InputInterface  $input  An input stream
     * @param OutputInterface $output An output stream
     */
    protected function initializeLicenser(InputInterface $input, OutputInterface $output)
    {
        $license = $input->getArgument('license');

        try {
            $licenseHeader = $this->licenseHeaderFactory->createFromLicenseName(
                $license,
                ['owners' => $input->getOption('owners')]
            );
        } catch (\InvalidArgumentException $e) {
            $licenseHeader = file_get_contents($license);
        }

        $this->licenser->setLicenseHeader($licenseHeader);
        $this->licenser->setOutputStream($output);
    }
}
