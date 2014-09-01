Getting Started With ZebbaFilterBundle
======================================

## Installation

###Step 1: Download ZebbaFilterBundle using composer

Modify your composer.json to add the ZebbaFilterBundle:

```json
{
    "require" : {
        "zebba/filterhandler-bundle" : "~1.0"
    },
    "repositories" : [
        {
            "type" : "vcs",
            "url" : "https://github.com/zebba/FilterBundle.git"
        }        
    ]
}    
```

Run the following command to have composer download the bundle:

``` bash
$ php composer.phar update
```

###Step 2: Enable the bundle

Enable the bundle in the kernel:

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Zebba\Bundle\FilterBundle\ZebbaFilterBundle(),
    );
}
```

## Creating a filter

A filter in the context of this bundle is a combination of different things:

1. A model for storing the data
2. A FormType defining the fields of the filter
3. A FilterHandler dealing with the generation of the form and binding the request
4. A FilterManager that connects the FilterHandler, the Session and your database together

###Step 1: Create your filter model

Imagine you have written a BlogBundle which has an entity Post:

``` php
<?php

// src/Acme/BlogBundle/Entity/Post

namespace Acme\BlogBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;

class Post
{
    /** @var UserInterface */
    private $author;
    /** @var Collection */
    private $categories;
    
    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }
    
    // ...
}
```

In order to filter the Posts by these two properties let's start by creating a 
basic class that implements the FilterInterface:

``` php
<?php
// src/Acme/BlogBundle/Model/Filter/PostFilter

namespace Acme\BlogBundle\Model\Filter;

use Acme\BlogBundle\Entity\Category;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Security\Core\User\UserInterface;
use Zebba\Component\Form\Filter\FilterInterface;

class PostFilter implements FilterInterface
{
    /** @var UserInterface */
    private $author;
    /** @var Collection */
    private $categories;

    public function __construct()
    {
        $this->categories = new ArrayCollection;
    }

    public function getFilter()
    {
        /* @var $categories Collection */
        $categories = $this->categories->map(function ($e) { /* @var $e Category */
            return $e->getCategory(); // pk in  Category is 'category'
        });
        
        return array(
            'author' => (isset($this->author)) ? $this->author->getUsername() : null, // pk is 'username'
            'categories' => $categories->toArray(),
        );
    }
    
    public function isEmpty()
    {
        return (! $this->categories->isEmpty() && ! isset($this->author);
    }
    
    public function reset()
    {
        $this->author = null;
        $this->categories = new ArrayCollection;
    }
    
    public function getAuthor() { /* ... */ }
    public function setAuthor(UserInterface $author) { /* ... */ }
    public function getCategories() { /* ... */ }
    public function setCategories(Collection $categories) { /* ... */ }
    public function addCategory(Categoriy $category) { /* ... */ }
    public function removeCategory(Category $category) { /* ... */ }
}
```

The function getFilter() will return an array with all the primary key values you stored in the filter, isEmpty()
checks if there is actually any data set in the filter.

In order to make some magic happen, we also need to help the ZebbaFilterBundle and tell it what kind of
entities the property holds by adding the Filter annotation:


``` php
<?php
// src/Acme/BlogBundle/Model/Filter/PostFilter

namespace Acme\BlogBundle\Model\Filter;

// ...
use Zebba\Bundle\FilterBundle\Annotation\Filter;

class PostFilter implements FilterInterface
{
    // ...
    
    /**
     * @var UserInterface
     *
     * @Filter(targetEntity="Acme\BlogBundle\Entity\Author") 
     */
    private $author;
    
    /**
     * @var Collection
     *
     * @Filter(targetEntity="Acme\BlogBundle\Entity\Category") 
     */
    private $categories;
    
    // ...
}
```
###Step 2: Create the FormType

Next on the list is the FormType:

````php
<?php 

namespace Acme\BlogBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class PostFilterType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author', 'entity', array(
                'class' => 'Acme\BlogBundle\Entity\Author',
                'multiple' => false,
            ))
            ->add('categories', 'entity', array(
                'class' => 'Acme\BlogBundle\Entity\Category',
                'multiple' => true,
            ))
        ;
    }
    
    /**
     * @param OptionsResolverInterface $resolver
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Acme\BlogBundle\Model\Filter\PostFilter',
        ));
    }
    
    /**
     * @return string
     */
    public function getName()
    {
        return 'acme_blog_post';
    }
}
```

Your FormType needs to be defined as a service:

``` xml
<!-- src/Acme/BlogBundle/Resources/config/services.xml -->

<services>
    <service id="acme_blog.post_filter.form_type" class="Acme\BlogBundle\Form\PostFilterType" />
</services>
```

### Step 3: Setup the FilterHandler

Next on the list is creating the FilterHandler:

``` xml
<!-- src/Acme/BlogBundle/Resources/config/services.xml -->

<services>
    <service id="acme_blog.filter.handler.post"
        class="Zebba\Component\Form\Handler\FilterHandler"
        factory-service="zebba_filter.handler_factory"
        factory-method="get">
        <argument>acme_blog.filter.post</argument>
        <argument type="service" id="acme_blog.post_filter.form_type" />
    </service>
</services>
```

### Step 4: Setup the FilterManager

We are almost there! Add the definiton for the FilterManager:

``` xml
<!-- src/Acme/BlogBundle/Resources/config/services.xml -->

<services>
    <service id="acme_blog.filter.manager.post"
        class="Zebba\Bundle\FilterBundle\Model\FilterManager"
        factory-service="zebba_filter.manager_factory"
        factory-method="get">
        <argument>acme_blog.filter.post</argument>
        <argument type="service" id="acme_blog.filter.handler.post" />
    </service>
</services>
```

## Usage

To make good use of your Filter you need to create a dedicated function in your Repository.

In your Controller you can then use:

``` php
<?php

// src/Acme/BlogBundle/Controller/PostController

use Acme\BlogBundle\Entity\Post;
use Acme\BlogBundle\Model\Filter\PostFilter;
use Symfony\Component\HttpFoundation\Request;
use Zebba\Bundle\FilterBundle\Model\FilterManager;

// ...

public function indexAction(Request $request)
{
    $manager = $this->get('acme_blog.filter.manager.post');
    
    /* @var $filter PostFilter */
    $filter = new PostFilter;
        
    $em = $this->getDoctrine()->getManager();    
        
    if ($manager->process($filter, array(), $request)) {
        /* @var $posts Post[] */
        $posts = $em->getRepository('AcmeBlogBundle:Post')->getByFilter($filter);
    } else {
        /* @var $posts Post[] */
        $posts = $em->getRepository('AcmeBlogBundle:Post')->findAll();
    }
        
    return $this->render('AcmeBlogBundle:Post:index.html.twig', array(
        'posts' =>$posts, 
        'filter' => $this->getFilter($filter)->createView(),
    ));
}

private function getFilter(PostFilter $filter)
{
    return $this->get('acme_blog.filter.manager.blog')->generateForm($filter, array(), 'POST',
        $this->generateUrl('blog'),
        'Filter');
}
```

## Default Configuration

If you want to modify the configuration you can easily swap out parameters.

````yml
# app/config/config.yml
parameters:
    zebba_filter:
        handler_factory.class: Zebba\Bundle\FilterBundle\Factory\FilterHandlerFactory
        manager_factory.class: Zebba\Bundle\FilterBundle\Factory\FilterManagerFactory

services:  
    zebba_filter.service.form_factory: { alias: form.factory }
    zebba_filter.service.object_manager: { alias: doctrine.orm.entity_manager }
    zebba_filter.service.annotation_reader: { alias: annotation_reader }
    zebba_filter.service.session: { alias: session }
```
