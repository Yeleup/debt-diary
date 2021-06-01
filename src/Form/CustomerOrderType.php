<?php

namespace App\Form;

use App\Entity\CustomerOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class CustomerOrderType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', MoneyType::class, ['currency' => 'KZT', 'label_format' => $this->translator->trans('customer_order.amount')])
            ->add('type', null, ['label_format' => $this->translator->trans('customer_order.type')])
            ->add('payment', null, ['label_format' => $this->translator->trans('customer_order.payment')])
            ->add('updated', DateTimeType::class, [
                'label_format' => $this->translator->trans('customer_order.updated'),
                'widget' => 'single_text',
                'html5' => true,
                'attr' => ['class' => 'js-datepicker'],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerOrder::class,
        ]);
    }
}
