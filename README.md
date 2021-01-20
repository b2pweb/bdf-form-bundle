# BDF Form Bundle

Bundle for [BDF Form](https://github.com/b2pweb/bdf-form)

[![Build Status](https://travis-ci.org/b2pweb/bdf-form-bundle.svg?branch=master)](https://travis-ci.org/b2pweb/bdf-form-bundle)
[![Packagist Version](https://img.shields.io/packagist/v/b2pweb/bdf-form-bundle.svg)](https://packagist.org/packages/b2pweb/bdf-form-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/b2pweb/bdf-form-bundle.svg)](https://packagist.org/packages/b2pweb/bdf-form-bundle)

## Installation

```
composer require b2pweb/bdf-form-bundle
```

And then add to `config/bundles.php` :

```php
<?php

return [
    // ...
    Bdf\Form\Bundle\FormBundle::class => ['all' => true],
];
```

## Configuration

To enable auto registration of custom forms and builders, simply enable `autoconfigure` and load the package sources :

```yaml
services:
    _defaults:
        autowire: true
        autoconfigure: true

    App\Form\:
        resource: './src/Form/*'
```

To use CSRF, do not forget to enable the CSRF service :

```yaml
framework:
    csrf_protection:
        enabled: true
```

## Usage

Simply use the container to instantiate the custom form.

> Note: The container will automatically inject all dependencies

```php
// Declare the form
class MyForm extends \Bdf\Form\Custom\CustomForm
{
    /**
     * @var MyService 
     */
    private $service;
    
    // You can declare dependencies on the constructor 
    public function __construct(MyService $service, ?\Bdf\Form\Aggregate\FormBuilderInterface $builder = null) 
    {
        parent::__construct($builder);
        
        $this->service = $service;
    }

    protected function configure(\Bdf\Form\Aggregate\FormBuilderInterface $builder) : void
    {
        // Configure fields
    }
}

// The controller
class MyController extends AbstractController
{
    public function save(Request $request)
    {
        // Create the form using the container
        $form = $this->container->get(MyForm::class);
        
        // Submit data
        if (!$form->submit($request->request->all())->valid()) {
            throw new FormError($form->error());
        }
        
        $this->service->save($form->value());
        
        return new Reponse('ok');
    }
}
```
