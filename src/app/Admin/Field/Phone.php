<?php

namespace App\Admin\Field;

use Encore\Admin\Form\Field\Mobile;

class Phone extends Mobile
{
    /**
     * Form element classes.
     *
     * @var array
     */
    protected $elementClass = ['js-phone-input'];

    /**
     * Options for specify elements.
     *
     * @var array
     */
    protected $options = ['mask' => null];

    /**
     * Element attributes.
     *
     * @var array
     */
    protected $attributes = ['data-code' => 'BY'];
}
