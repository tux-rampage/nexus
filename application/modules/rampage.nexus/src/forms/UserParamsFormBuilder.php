<?php
/**
 * Copyright (c) 2014 Axel Helmert
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Axel Helmert
 * @copyright Copyright (c) 2014 Axel Helmert
 * @license   http://www.gnu.org/licenses/gpl-3.0.txt GNU General Public License
 */

namespace rampage\nexus\forms;

use rampage\nexus\entities\Application;
use rampage\nexus\PackageInstallerInterface;
use rampage\nexus\DeployParameter;

use Zend\Form\Form;
use Zend\Form\Element as element;

use Zend\InputFilter\InputFilter;
use Zend\InputFilter\Input;


class UserParamsFormBuilder
{
    /**
     * @var ApplicationInstance
     */
    protected $application = null;

    /**
     * @param Application $application
     * @return self
     */
    public function setApplication(Application $application)
    {
        $this->application = $application;
        return $this;
    }

    /**
     * @param PackageInstallerInterface $package
     */
    public function createForm(PackageInstallerInterface $package)
    {
        $form = new Form();
        $inputFilter = new InputFilter();
        $previous = array();

        if ($this->application && $this->application->getCurrentVersion()) {
            $previous = $this->application->getCurrentVersion()->getUserParameters();
        }

        foreach ($package->getParameters() as $userParameter) {
            $name = $userParameter->getName();
            $input = new Input($name);
            $value = $userParameter->getDefaultValue();

            $input->setValidatorChain($userParameter->getValidatorChain());

            if (isset($previous[$name])) {
                $value = $previous[$name];
            }

            switch ($userParameter->getType()) {
                case DeployParameter::TYPE_CHECKBOX:
                    $element = new element\Checkbox($name);
                    break;

                case DeployParameter::TYPE_PASSWORD:
                    $element = new element\Password($name);
                    break;

                case DeployParameter::TYPE_SELECT:
                    $element = new element\Select($name);
                    break;

                case DeployParameter::TYPE_TEXT: // break intentionally omitted
                default:
                    $element = new element\Text($name);
                    break;
            }

            $element->setLabel($userParameter->getLabel())
                ->setOptions($userParameter->getOptions());

            if ($value !== null) {
                $element->setValue($value);
            }

            $input->setRequired($userParameter->isRequired());
            $input->setAllowEmpty(false);

            $form->add($element);
            $inputFilter->add($input);
        }

        $form->setInputFilter($inputFilter);
        return $form;
    }
}
