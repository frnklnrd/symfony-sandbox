<?php

namespace Yokai\SonataWorkflow\Admin\Extension;

use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AbstractAdminExtension;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Workflow\Exception\InvalidArgumentException;
use Symfony\Component\Workflow\Registry;
use Symfony\Component\Workflow\Transition;
use Symfony\Component\Workflow\Workflow;

/**
 * @author Yann Eugoné <eugone.yann@gmail.com>
 */
class WorkflowExtension extends AbstractAdminExtension
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var array
     */
    private $options;

    /**
     * @param Registry $registry
     * @param array    $options
     */
    public function __construct(Registry $registry, array $options = [])
    {
        $this->registry = $registry;
        $this->configureOptions($resolver = new OptionsResolver());
        $this->options = $resolver->resolve($options);
    }

    /**
     * @inheritdoc
     */
    public function configureRoutes(AdminInterface $admin, RouteCollection $collection)
    {
        $collection->add(
            'workflow_apply_transition',
            $admin->getRouterIdParameter() . '/workflow/transition/{transition}/apply'
        );
    }

    /**
     * @inheritdoc
     */
    public function alterNewInstance(AdminInterface $admin, $object)
    {
        try {
            $workflow = $this->getWorkflow($object, $this->options['workflow_name']);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $workflow->getMarking($object);
    }

    /**
     * @inheritdoc
     */
    public function configureSideMenu(
        AdminInterface $admin,
        MenuItemInterface $menu,
        $action,
        AdminInterface $childAdmin = null
    ) {
        $subject = $admin->getSubject();
        if (null === $subject) {
            return;
        }

        try {
            $workflow = $this->getWorkflow($subject, $this->options['workflow_name']);
        } catch (InvalidArgumentException $exception) {
            return;
        }

        $transitions = $workflow->getEnabledTransitions($subject);

        if (count($transitions) === 0) {
            $this->noTransitions($menu);
        } else {
            $this->transitionsDropdown($menu, $admin, $transitions, $subject);
        }
    }

    /**
     * @param object      $subject
     * @param string|null $workflowName
     *
     * @return Workflow
     * @throws InvalidArgumentException
     */
    protected function getWorkflow($subject, $workflowName = null)
    {
        return $this->registry->get($subject, $workflowName);
    }

    /**
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'workflow_name' => null,
                'no_transition_display' => false,
                'no_transition_label' => 'workflow_transitions_empty',
                'no_transition_icon' => 'fa fa-code-fork',
                'dropdown_transitions_label' => 'workflow_transitions',
                'dropdown_transitions_icon' => 'fa fa-code-fork',
                'transitions_default_icon' => null,
                'transitions_icons' => [],
            ])
            ->setAllowedTypes('workflow_name', ['string', 'null'])
            ->setAllowedTypes('no_transition_display', ['bool'])
            ->setAllowedTypes('no_transition_label', ['string'])
            ->setAllowedTypes('no_transition_icon', ['string'])
            ->setAllowedTypes('dropdown_transitions_label', ['string'])
            ->setAllowedTypes('dropdown_transitions_icon', ['string', 'null'])
            ->setAllowedTypes('transitions_default_icon', ['string', 'null'])
            ->setAllowedTypes('transitions_icons', ['array'])
        ;
    }

    /**
     * @param MenuItemInterface $menu
     */
    protected function noTransitions(MenuItemInterface $menu)
    {
        if ($this->options['no_transition_display']) {
            $menu->addChild($this->options['no_transition_label'], [
                'uri' => '#',
                'attributes' => [
                    'icon' => $this->options['no_transition_icon'],
                ],
            ]);
        }
    }

    /**
     * @param MenuItemInterface $menu
     * @param AdminInterface    $admin
     * @param Transition[]      $transitions
     * @param object            $subject
     */
    protected function transitionsDropdown(MenuItemInterface $menu, AdminInterface $admin, $transitions, $subject)
    {
        $workflowMenu = $menu->addChild($this->options['dropdown_transitions_label'], [
            'attributes' => [
                'dropdown' => true,
                'icon' => $this->options['dropdown_transitions_icon'],
            ],
        ]);

        foreach ($transitions as $transition) {
            $this->transitionsItem($workflowMenu, $admin, $transition, $subject);
        }
    }

    /**
     * @param MenuItemInterface $menu
     * @param AdminInterface    $admin
     * @param Transition        $transition
     * @param object            $subject
     */
    protected function transitionsItem(MenuItemInterface $menu, AdminInterface $admin, Transition $transition, $subject)
    {
        $options = [
            'uri' => $this->generateTransitionUri($admin, $transition, $subject),
            'attributes' => [],
        ];

        if ($icon = $this->getTransitionIcon($transition)) {
            $options['attributes']['icon'] = $icon;
        }

        $menu->addChild(
            $admin->getLabelTranslatorStrategy()->getLabel($transition->getName(), 'workflow'),
            $options
        );
    }

    /**
     * @param Transition $transition
     *
     * @return string|null
     */
    protected function getTransitionIcon(Transition $transition)
    {
        if (isset($this->options['transitions_icons'][$transition->getName()])) {
            return $this->options['transitions_icons'][$transition->getName()];
        }

        return $this->options['transitions_default_icon'];
    }

    /**
     * @param AdminInterface $admin
     * @param Transition     $transition
     * @param object         $subject
     *
     * @return string
     */
    protected function generateTransitionUri(AdminInterface $admin, Transition $transition, $subject)
    {
        return $admin->generateObjectUrl(
            'workflow_apply_transition',
            $subject,
            ['transition' => $transition->getName()]
        );
    }
}
