<?php

namespace ArcaSolutions\WebBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class TagPictureExtension
 * @package ArcaSolutions\WebBundle\Twig\Extension
 */
final class TagPictureExtension extends \Twig_Extension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'tagPicture';
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('tagPicture', [$this, 'tagPicture'], [
                'is_safe'           => ['html']
            ])
        ];
    }

    /**
     * Creates a tag picture set applying the filters passed in {
     * @param string $imagePath is the relative path of original image.
     * @param string $title is the text to be show in <code>alt</code> attribute.
     * @param array $deviceFilter is a map with _device_ as key and _filter_ as value to be used,
     * eg.: <code>{'mobile': 'large'}</code>.
     * @param string $template
     * @param null $itemId
     * @param null $class
     * @return string
     * @throws \Twig_Error @see <a href='https://developer.mozilla.org/en-US/docs/Web/HTML/Element/picture}'>https://developer.mozilla.org/en-US/docs/Web/HTML/Element/picture}</a>
     */
    public function tagPicture(string $imagePath, string $title, array $deviceFilter = [], $template = 'picture', $itemId = null, $class = null)
    {
        $data = $this->container->get('tag.picture.service')->getImageSource($imagePath, $title, $deviceFilter);

        !empty($itemId) and $data['itemId'] = $itemId;
        !empty($class) and $data['class'] = $class;

        return $this->container->get('templating')->render("@Web/images/$template.html.twig", $data);
    }
}
