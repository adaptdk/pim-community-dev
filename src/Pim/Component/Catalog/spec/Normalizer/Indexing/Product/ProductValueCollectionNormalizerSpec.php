<?php

namespace spec\Pim\Component\Catalog\Normalizer\Indexing\Product;

use PhpSpec\ObjectBehavior;
use Pim\Component\Catalog\Model\AttributeInterface;
use Pim\Component\Catalog\Model\ProductValue;
use Pim\Component\Catalog\Model\ProductValueCollection;
use Pim\Component\Catalog\Normalizer\Indexing\Product\ProductValueCollectionNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ProductValueCollectionNormalizerSpec extends ObjectBehavior
{
    function let(
        SerializerInterface $serializer,
        ProductValueCollection $productValueCollection
    ) {
        $serializer->implement(NormalizerInterface::class);
        $this->setSerializer($serializer);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(ProductValueCollectionNormalizer::class);
    }

    function it_support_product_value_collection($productValueCollection)
    {
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'indexing')->shouldReturn(false);
        $this->supportsNormalization($productValueCollection, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($productValueCollection, 'indexing')->shouldReturn(true);
    }

    function it_normalize_product_value_collection(
        $productValueCollection,
        ProductValue $productValue1,
        ProductValue $productValue2,
        AttributeInterface $attribute1,
        AttributeInterface $attribute2,
        \ArrayIterator $productValueCollectionIterator,
        SerializerInterface $serializer
    ) {
        $productValueCollection->getIterator()->willReturn($productValueCollectionIterator);
        $productValueCollectionIterator->rewind()->shouldBeCalled();
        $productValueCollectionIterator->valid()->willReturn(true, true, false);
        $productValueCollectionIterator->current()->willReturn($productValue1, $productValue2);

        $productValueCollectionIterator->next()->shouldBeCalled();

        $productValue1->getAttribute()->willReturn($attribute1);
        $productValue2->getAttribute()->willReturn($attribute2);

        $attribute1->getType()->willReturn('pim_catalog_number');
        $attribute2->getType()->willReturn('pim_catalog_text');

        $serializer->normalize($productValue1, 'indexing', [])->willReturn(
            [
                'box_quantity-decimal' => [
                    '<all_locales>' => [
                        '<all_channels>' => '7',
                    ],
                ],
            ]
        );

        $serializer->normalize($productValue2, 'indexing', [])->willReturn(
            [
                'description-text' => [
                    '<all_locales>' => [
                        '<all_channels>' => 'Nice description for phpspec',
                    ],
                ],
            ]
        );

        $this->normalize($productValueCollection, 'indexing',[])->shouldReturn(
            [
                'box_quantity-decimal' => [
                    '<all_locales>' => [
                        '<all_channels>' => '7',
                    ],
                ],
                'description-text'     => [
                    '<all_locales>' => [
                        '<all_channels>' => 'Nice description for phpspec',
                    ],
                ],
            ]
        );
    }
}
