<?php

// stolen from Psalm

/**
 * @template TKey
 * @template TValue
 * @template TSend
 * @template TReturn
 *
 * @template-implements Traversable<TKey, TValue>
 */
class Generator implements Traversable
{
    /**
     * @return ?TValue Can return any type.
     */
    public function current()
    {
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function next(): void
    {
    }

    /**
     * @return TKey scalar on success, or null on failure.
     */
    public function key()
    {
    }

    /**
     * @return bool The return value will be casted to boolean and then evaluated.
     */
    public function valid()
    {
    }

    /**
     * @return void Any returned value is ignored.
     */
    public function rewind(): void
    {
    }

    /**
     * @return TReturn Can return any type.
     */
    public function getReturn()
    {
    }

    /**
     * @param TSend $value
     * @return ?TValue Can return any type.
     */
    public function send($value)
    {
    }

    /**
     * @return ?TValue Can return any type.
     */
    public function throw(Throwable $exception)
    {
    }
}

/**
 * Interface to provide accessing objects as arrays.
 * @link http://php.net/manual/en/class.arrayaccess.php
 *
 * @template TKey
 * @template TValue
 */
interface ArrayAccess
{
    /**
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param TKey $offset An offset to check for.
     * @return bool true on success or false on failure.
     *              The return value will be casted to boolean if non-boolean was returned.
     *
     * @since 5.0.0
     */
    public function offsetExists($offset);

    /**
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param TKey $offset The offset to retrieve.
     * @return TValue|null Can return all value types.
     *
     * @since 5.0.0
     */
    public function offsetGet($offset);

    /**
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param TKey|null $offset The offset to assign the value to.
     * @param TValue $value The value to set.
     *
     * @since 5.0.0
     */
    public function offsetSet($offset, $value): void;

    /**
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param TKey $offset The offset to unset.
     *
     * @since 5.0.0
     */
    public function offsetUnset($offset): void;
}

/**
 * This class allows objects to work as arrays.
 * @link http://php.net/manual/en/class.arrayobject.php
 *
 * @template TKey
 * @template TValue
 * @template-implements IteratorAggregate<TKey, TValue>
 * @template-implements ArrayAccess<TKey, TValue>
 */
class ArrayObject implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    /**
     * Properties of the object have their normal functionality when accessed as list (var_dump, foreach, etc.).
     */
    const STD_PROP_LIST = 1;

    /**
     * Entries can be accessed as properties (read and write).
     */
    const ARRAY_AS_PROPS = 2;

    /**
     * Construct a new array object
     * @link http://php.net/manual/en/arrayobject.construct.php
     *
     * @param array<TKey, TValue>|object $input The input parameter accepts an array or an Object.
     * @param int $flags Flags to control the behaviour of the ArrayObject object.
     * @param string $iterator_class Specify the class that will be used for iteration of the ArrayObject object. ArrayIterator is the default class used.
     *
     * @since 5.0.0
     */
    public function __construct($input = null, $flags = 0, $iterator_class = 'ArrayIterator')
    {
    }

    /**
     * Returns whether the requested index exists
     * @link http://php.net/manual/en/arrayobject.offsetexists.php
     *
     * @param TKey $index The index being checked.
     * @return bool true if the requested index exists, otherwise false
     *
     * @since 5.0.0
     */
    public function offsetExists($index)
    {
    }

    /**
     * Returns the value at the specified index
     * @link http://php.net/manual/en/arrayobject.offsetget.php
     *
     * @param TKey $index The index with the value.
     * @return TValue The value at the specified index or false.
     *
     * @since 5.0.0
     */
    public function offsetGet($index)
    {
    }

    /**
     * Sets the value at the specified index to newval
     * @link http://php.net/manual/en/arrayobject.offsetset.php
     *
     * @param TKey $index  The index being set.
     * @param TValue $newval The new value for the index.
     *
     * @since 5.0.0
     */
    public function offsetSet($index, $newval): void
    {
    }

    /**
     * Unsets the value at the specified index
     * @link http://php.net/manual/en/arrayobject.offsetunset.php
     *
     * @param TKey $index The index being unset.
     *
     * @since 5.0.0
     */
    public function offsetUnset($index): void
    {
    }

    /**
     * Appends the value
     * @link http://php.net/manual/en/arrayobject.append.php
     *
     * @param TValue $value The value being appended.
     *
     * @since 5.0.0
     */
    public function append($value): void
    {
    }

    /**
     * Creates a copy of the ArrayObject.
     * @link http://php.net/manual/en/arrayobject.getarraycopy.php
     *
     * @return array<TKey, TValue>  a copy of the array. When the ArrayObject refers to an object
     *                              an array of the public properties of that object will be returned.
     *
     * @since 5.0.0
     */
    public function getArrayCopy()
    {
    }

    /**
     * Get the number of public properties in the ArrayObject
     * When the ArrayObject is constructed from an array all properties are public.
     * @link http://php.net/manual/en/arrayobject.count.php
     *
     * @return int The number of public properties in the ArrayObject.
     *
     * @since 5.0.0
     */
    public function count()
    {
    }

    /**
     * Gets the behavior flags.
     * @link http://php.net/manual/en/arrayobject.getflags.php
     *
     * @return int the behavior flags of the ArrayObject.
     *
     * @since 5.1.0
     */
    public function getFlags()
    {
    }

    /**
     * Sets the behavior flags.
     *
     * It takes on either a bitmask, or named constants. Using named
     * constants is strongly encouraged to ensure compatibility for future
     * versions.
     *
     * The available behavior flags are listed below. The actual
     * meanings of these flags are described in the
     * predefined constants.
     *
     * <table>
     * ArrayObject behavior flags
     * <tr valign="top">
     * <td>value</td>
     * <td>constant</td>
     * </tr>
     * <tr valign="top">
     * <td>1</td>
     * <td>
     * ArrayObject::STD_PROP_LIST
     * </td>
     * </tr>
     * <tr valign="top">
     * <td>2</td>
     * <td>
     * ArrayObject::ARRAY_AS_PROPS
     * </td>
     * </tr>
     * </table>
     *
     * @link http://php.net/manual/en/arrayobject.setflags.php
     *
     * @param int $flags The new ArrayObject behavior.
     *
     * @since 5.1.0
     */
    public function setFlags($flags): void
    {
    }

    /**
     * Sort the entries by value
     * @link http://php.net/manual/en/arrayobject.asort.php
     *
     *
     * @since 5.2.0
     */
    public function asort(): void
    {
    }

    /**
     * Sort the entries by key
     * @link http://php.net/manual/en/arrayobject.ksort.php
     *
     *
     * @since 5.2.0
     */
    public function ksort(): void
    {
    }

    /**
     * Sort the entries with a user-defined comparison function and maintain key association
     * @link http://php.net/manual/en/arrayobject.uasort.php
     *
     * Function <i>cmp_function</i> should accept two
     * parameters which will be filled by pairs of entries.
     * The comparison function must return an integer less than, equal
     * to, or greater than zero if the first argument is considered to
     * be respectively less than, equal to, or greater than the
     * second.
     *
     * @param callable(TValue, TValue):int $cmp_function
     *
     * @since 5.2.0
     */
    public function uasort($cmp_function): void
    {
    }

    /**
     * Sort the entries by keys using a user-defined comparison function
     * @link http://php.net/manual/en/arrayobject.uksort.php
     *
     * Function <i>cmp_function</i> should accept two
     * parameters which will be filled by pairs of entry keys.
     * The comparison function must return an integer less than, equal
     * to, or greater than zero if the first argument is considered to
     * be respectively less than, equal to, or greater than the
     * second.
     *
     * @param callable(TKey, TKey):int $cmp_function The callable comparison function.
     *
     * @since 5.2.0
     */
    public function uksort($cmp_function): void
    {
    }

    /**
     * Sort entries using a "natural order" algorithm
     * @link http://php.net/manual/en/arrayobject.natsort.php
     *
     *
     * @since 5.2.0
     */
    public function natsort(): void
    {
    }

    /**
     * Sort an array using a case insensitive "natural order" algorithm
     * @link http://php.net/manual/en/arrayobject.natcasesort.php
     *
     *
     * @since 5.2.0
     */
    public function natcasesort(): void
    {
    }

    /**
     * Unserialize an ArrayObject
     * @link http://php.net/manual/en/arrayobject.unserialize.php
     *
     * @param string $serialized  The serialized ArrayObject
     * @return void The unserialized ArrayObject
     *
     * @since 5.3.0
     */
    public function unserialize($serialized): void
    {
    }

    /**
     * Serialize an ArrayObject
     * @link http://php.net/manual/en/arrayobject.serialize.php
     *
     * @return string The serialized representation of the ArrayObject.
     *
     * @since 5.3.0
     */
    public function serialize()
    {
    }

    /**
     * Create a new iterator from an ArrayObject instance
     * @link http://php.net/manual/en/arrayobject.getiterator.php
     *
     * @return ArrayIterator<TKey, TValue> An iterator from an ArrayObject.
     *
     * @since 5.0.0
     */
    public function getIterator()
    {
    }

    /**
     * Exchange the array for another one.
     * @link http://php.net/manual/en/arrayobject.exchangearray.php
     *
     * @param mixed $input The new array or object to exchange with the current array.
     * @return array the old array.
     *
     * @since 5.1.0
     */
    public function exchangeArray($input)
    {
    }

    /**
     * Sets the iterator classname for the ArrayObject.
     * @link http://php.net/manual/en/arrayobject.setiteratorclass.php
     *
     * @param string $iterator_class The classname of the array iterator to use when iterating over this object.
     *
     * @since 5.1.0
     */
    public function setIteratorClass($iterator_class): void
    {
    }

    /**
     * Gets the iterator classname for the ArrayObject.
     * @link http://php.net/manual/en/arrayobject.getiteratorclass.php
     *
     * @return string the iterator class name that is used to iterate over this object.
     *
     * @since 5.1.0
     */
    public function getIteratorClass()
    {
    }
}

interface Serializable
{
    /**
     * @return null|string
     */
    public function serialize();

    /**
     * @param string $data
     */
    public function unserialize($data): void;
}

/**
 * @template-covariant T as object
 */
final class WeakReference
{
    // always fail
    public function __construct()
    {
    }

    /**
     * @template TIn as object
     * @param TIn $referent
     * @return WeakReference<TIn>
     */
    public static function create(object $referent): WeakReference
    {
    }

    /** @return ?T */
    public function get(): ?object
    {
    }
}

/**
 * @template TKey of object
 * @template TVal of mixed
 * @implements ArrayAccess<TKey, TVal>
 * @implements IteratorAggregate<TKey,TVal>
 * @implements Traversable<TKey,TVal>
 *
 * @since 8.0.0
 */
final class WeakMap implements ArrayAccess, Countable, IteratorAggregate, Traversable
{
    /**
     * @param TKey $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
    }

    /**
     * @param TKey $offset
     * @return TVal|null
     */
    public function offsetGet($offset)
    {
    }

    /**
     * @param TKey $offset
     * @param TVal $value
     */
    public function offsetSet($offset, $value): void
    {
    }

    /**
     * @param TKey $offset
     */
    public function offsetUnset($offset): void
    {
    }
}


#[Attribute(Attribute::TARGET_METHOD)]
final class ReturnTypeWillChange
{
    public function __construct()
    {
    }
}

#[Attribute(Attribute::TARGET_PARAMETER)]
final class SensitiveParameter
{
    public function __construct()
    {
    }
}

#[Attribute(Attribute::TARGET_CLASS)]
final class AllowDynamicProperties
{
    public function __construct()
    {
    }
}
