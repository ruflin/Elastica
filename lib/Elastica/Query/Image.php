<?php

namespace Elastica\Query;

/**
 * Image query.
 *
 * @author   Jacques Moati <jacques@moati.net>
 *
 * @link     https://github.com/kzwang/elasticsearch-image
 *
 * To use this feature you have to call the following command in the
 * elasticsearch directory:
 * <code>
 * ./bin/plugin --url https://github.com/Jmoati/elasticsearch-image/releases/download/1.7.1/elasticsearch-image-1.7.1.zip --install image
 * </code>
 * This installs the image plugin. More infos
 * can be found here: {@link https://github.com/Jmoati/elasticsearch-image}
 */
class Image extends AbstractQuery
{
    public function __construct(array $image = array())
    {
        $this->setParams($image);
    }

    /**
     * Sets a param for the given field.
     *
     * @param string $field
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setFieldParam($field, $key, $value)
    {
        if (!isset($this->_params[$field])) {
            $this->_params[$field] = array();
        }

        $this->_params[$field][$key] = $value;

        return $this;
    }

    /**
     * Set field boost value.
     *
     * If not set, defaults to 1.0.
     *
     * @param string $field
     * @param float  $boost
     *
     * @return $this
     */
    public function setFieldBoost($field, $boost = 1.0)
    {
        return $this->setFieldParam($field, 'boost', (float) $boost);
    }

    /**
     * Set field feature value.
     *
     * If not set, defaults CEDD.
     *
     * @param string $field
     * @param string $feature
     *
     * @return $this
     */
    public function setFieldFeature($field, $feature = 'CEDD')
    {
        return $this->setFieldParam($field, 'feature', $feature);
    }

    /**
     * Set field hash value.
     *
     * If not set, defaults BIT_SAMPLING.
     *
     * @param string $field
     * @param string $hash
     *
     * @return $this
     */
    public function setFieldHash($field, $hash = 'BIT_SAMPLING')
    {
        return $this->setFieldParam($field, 'hash', $hash);
    }

    /**
     * Set field image value.
     *
     * @param string $field
     * @param string $path  File will be base64_encode
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function setFieldImage($field, $path)
    {
        if (!file_exists($path) || !is_readable($path)) {
            throw new \Exception(sprintf("File %s can't be open", $path));
        }

        return $this->setFieldParam($field, 'image', base64_encode(file_get_contents($path)));
    }

    /**
     * Set field index value.
     *
     * @param string $field
     * @param string $index
     *
     * @return $this
     */
    public function setFieldIndex($field, $index)
    {
        return $this->setFieldParam($field, 'index', $index);
    }

    /**
     * Set field type value.
     *
     * @param string $field
     * @param string $type
     *
     * @return $this
     */
    public function setFieldType($field, $type)
    {
        return $this->setFieldParam($field, 'type', $type);
    }

    /**
     * Set field id value.
     *
     * @param string $field
     * @param string $id
     *
     * @return $this
     */
    public function setFieldId($field, $id)
    {
        return $this->setFieldParam($field, 'id', $id);
    }

    /**
     * Set field path value.
     *
     * @param string $field
     * @param string $path
     *
     * @return $this
     */
    public function setFieldPath($field, $path)
    {
        return $this->setFieldParam($field, 'path', $path);
    }

    /**
     * Define quickly a reference image already in your elasticsearch database.
     *
     * If not set, path will be the same as $field.
     *
     * @param string $field
     * @param string $index
     * @param string $type
     * @param string $id
     * @param string $path
     *
     * @return $this
     */
    public function setImageByReference($field, $index, $type, $id, $path = null)
    {
        if (null === $path) {
            $path = $field;
        }

        $this->setFieldIndex($field, $index);
        $this->setFieldType($field, $type);
        $this->setFieldId($field, $id);

        return $this->setFieldPath($field, $path);
    }
}
