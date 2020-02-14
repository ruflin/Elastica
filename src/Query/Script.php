<?php

namespace Elastica\Query;

use Elastica\Script\AbstractScript;
use Elastica\Script\Script as BaseScript;

/**
 * Script query.
 *
 * @author Nicolas Ruflin <spam@ruflin.com>
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-script-query.html
 */
class Script extends AbstractQuery
{
    /**
     * Construct script query.
     *
     * @param AbstractScript|array|string $script Script
     */
    public function __construct($script = null)
    {
        if (null !== $script) {
            $this->setScript($script);
        }
    }

    /**
     * Sets script object.
     *
     * @param array|BaseScript|string $script
     *
     * @return $this
     */
    public function setScript($script): self
    {
        return $this->setParam('script', BaseScript::create($script));
    }

    /**
     * {@inheritdoc}
     */
    public function toArray(): array
    {
        $array = parent::toArray();

        if (isset($array['script'])) {
            $array['script'] = $array['script']['script'];
        }

        return $array;
    }
}
