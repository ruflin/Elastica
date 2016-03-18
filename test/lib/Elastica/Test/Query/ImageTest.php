<?php

namespace Elastica\Test\Query;

use Elastica\Document;
use Elastica\Index;
use Elastica\Query\Image;
use Elastica\Test\Base as BaseTest;
use Elastica\Type;
use Elastica\Type\Mapping;

class ImageTest extends BaseTest
{
    /**
     * @var string
     */
    protected $_testFileContent;

    protected function setUp()
    {
        parent::setUp();
        $this->_testFileContent = base64_encode(file_get_contents(BASE_PATH.'/data/test.jpg'));
    }

    /**
     * @group unit
     */
    public function testToArrayFromReference()
    {
        $client = $this->_getClient();
        $index = new Index($client, 'test');
        $type = new Type($index, 'helloworld');
        $field = 'image';

        $query = new Image();
        $query->setFieldFeature($field, 'CEDD');
        $query->setFieldHash($field, 'BIT_SAMPLING');
        $query->setFieldBoost($field, 100);

        $query->setImageByReference($field, $index->getName(), $type->getName(), 10);

        $jsonString = '{"image":{"image":{"feature":"CEDD","hash":"BIT_SAMPLING","boost":100,"index":"test","type":"helloworld","id":10,"path":"image"}}}';
        $this->assertEquals($jsonString, json_encode($query->toArray()));
    }

    /**
     * @group unit
     */
    public function testToArrayFromImage()
    {
        $field = 'image';

        $query = new Image();
        $query->setFieldFeature($field, 'CEDD');
        $query->setFieldHash($field, 'BIT_SAMPLING');
        $query->setFieldBoost($field, 100);

        $query->setFieldImage($field, BASE_PATH.'/data/test.jpg');

        $jsonString = '{"image":{"image":{"feature":"CEDD","hash":"BIT_SAMPLING","boost":100,"image":"\/9j\/4QAYRXhpZgAASUkqAAgAAAAAAAAAAAAAAP\/sABFEdWNreQABAAQAAAA8AAD\/4QN6aHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wLwA8P3hwYWNrZXQgYmVnaW49Iu+7vyIgaWQ9Ilc1TTBNcENlaGlIenJlU3pOVGN6a2M5ZCI\/PiA8eDp4bXBtZXRhIHhtbG5zOng9ImFkb2JlOm5zOm1ldGEvIiB4OnhtcHRrPSJBZG9iZSBYTVAgQ29yZSA1LjUtYzAyMSA3OS4xNTQ5MTEsIDIwMTMvMTAvMjktMTE6NDc6MTYgICAgICAgICI+IDxyZGY6UkRGIHhtbG5zOnJkZj0iaHR0cDovL3d3dy53My5vcmcvMTk5OS8wMi8yMi1yZGYtc3ludGF4LW5zIyI+IDxyZGY6RGVzY3JpcHRpb24gcmRmOmFib3V0PSIiIHhtbG5zOnhtcE1NPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvbW0vIiB4bWxuczpzdFJlZj0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL3NUeXBlL1Jlc291cmNlUmVmIyIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bXBNTTpPcmlnaW5hbERvY3VtZW50SUQ9InhtcC5kaWQ6OWQ4MjQ5N2MtNzViMS0wYzQ5LTg4ZjMtMDdiNmRhMjU0ZWRhIiB4bXBNTTpEb2N1bWVudElEPSJ4bXAuZGlkOjA4NjBGM0Y1QkJGQTExRTM4MjQ0QzMzNjU2MjUxOEJGIiB4bXBNTTpJbnN0YW5jZUlEPSJ4bXAuaWlkOjA4NjBGM0Y0QkJGQTExRTM4MjQ0QzMzNjU2MjUxOEJGIiB4bXA6Q3JlYXRvclRvb2w9IkFkb2JlIFBob3Rvc2hvcCBDQyAoV2luZG93cykiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5ZDgyNDk3Yy03NWIxLTBjNDktODhmMy0wN2I2ZGEyNTRlZGEiIHN0UmVmOmRvY3VtZW50SUQ9InhtcC5kaWQ6OWQ4MjQ5N2MtNzViMS0wYzQ5LTg4ZjMtMDdiNmRhMjU0ZWRhIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+\/+4ADkFkb2JlAGTAAAAAAf\/bAIQABgQEBAUEBgUFBgkGBQYJCwgGBggLDAoKCwoKDBAMDAwMDAwQDA4PEA8ODBMTFBQTExwbGxscHx8fHx8fHx8fHwEHBwcNDA0YEBAYGhURFRofHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8fHx8f\/8AAEQgAZABkAwERAAIRAQMRAf\/EAKwAAAEFAQEBAQAAAAAAAAAAAAgAAwQFBwYCAQkBAQEAAwEBAAAAAAAAAAAAAAABAgQFAwYQAAEDAgMCBQwOCQQDAAAAAAIBAwQABRESBiEHMUFhExRRcYEisnPDNIQVRRaRodEyQlJikiOzVGYnCLFyo9NklKQlGIIzQ2VTdhcRAAIBAgQEBAYDAQAAAAAAAAABAhEDITESBEFRMgVhcZFCgaHR4SITsVJiFf\/aAAwDAQACEQMRAD8AJyHDhlDYImG1JWwVVUBVVVRTkqge6DC+ztfMH3KgF0GF9na+YPuUAugwvs7XzB9ygF0GF9na+YPuUBR6n1NpDTTAuXUmgdcTFmK22JvuYfFbRMcOVdnLXje3MLa\/JmEpqOZmty32GbiparFGaa24OS1QzXlyNoiJ85a50+5y9q9Tyd7kiva3v6jQ8xwraY\/E5gh9tDrBdxueBP2s6ax74rK6YtXq0DFx2LJjIjzafrAooaJ1sa2LfcU+pUM1eXE0i3nY7jEblwUjyYzqYg62gEK+wnDyV0IzUlVOqPVNMkdBhfZ2vmD7lZFF0GF9na+YPuUBD6JF88c3zIc30fNkyphjnwxwwqkJkHxKP3oO5SoUfoBUAqA47eXr5rSlqEY6C7eJuIwmC2iKJ755xE+AGPZXZWpu9z+qOHU8jzuXNK8Qe813vd2UlV243WaWJmvbOGqcarsQQHsCKVw4Qnclh+UmamLZ3dn3UKQCd1lkprwx42CCnIrpIqqvWSuva7Uve\/Q9la5l0W6rT6hgCSQPiLnlL2iTCth9ss+PqX9aOcv27W725spEEimsDtJpRyvoicaInan2NvJWjf7dKKrB6l8\/uYuDRWaT1fdNN3BJcIlNhxU6XCVcG3hTh\/VcT4Jdhdlali+7bqsiRk0ETZrvBvFsj3KCfORZI5wVdipxKJJxEK7FSu9bmpxTWRtJ1VSbWZSF6b8m8JVA9B8Sj96DuUqAfoDNdab+dKaZvDtnGPIuk6MqDMSNkRtolTHIpuEKKaIqYoOOHHtoCz3bb07frsriMO3yIPm7ms\/SCbLPz2bDLkIuDJx0Bh2u9SHf9VXG5kalHEyYhp8WOwqiOCfKXE+zXzO4u67jkaM5VdTSt3ulAtdrB54E84zBFyUfGIr2wMovUFOHqlX0Gy2ytQ\/08\/oe8I0R3LTIinBW6ZEphGxLthx61Ggiw6Mw8zwIQrXk20zMxXexpMLZLG7xRysSTyShRMERxdouf68MC5a5HcLCi9a45+fP4nhcjTEsdx2oTbuEywun9DIBZcUV4nAVBdRP1kUS7FTt12jcfiWzLgbJXWNghem\/JvCVQPQfEo\/eg7lKgH6AC1m2u6h3hnajeVty63h9hySqZlFDkGpHgvCqCi4VQEvp3dvYNA2a+yLI7KcclxlN1ZTgubY7ZqCjlEMPfLjXncdIvyI8gfbLGR+4W1hzaDzzAn1lMca+b28azinzRoxWISEIUwxw4Vxr642mTxSqQfbCowTI+IqvUVNtYSM4nI70GG39JXNC4W2VcFeoQEhItae9VbMjG50syPdfKNnXtmUOFxxxov1TZPH9FcfaOl2J423+SCVr6A2yF6b8m8JVA9B8Sj96DuUqAfoAOtGF+MtrT\/v3vrXaoDAmRhlRH4x+9fbJsusYqK\/prGSqqBgjgj9ul82YqMm3vZCBeHPHcwVOyoV8zGsJeMX\/AAaGQRdjlNTobctgkJl4BdbVOMTTH2uCvrIyUkmsmbWZbtJjtrMhKbGsWyj2wRxrBmSM73v3lqHpd+Pm+muBDHaHjUcUJxewKVz+43KW6cZYfUwuukfM4PczbHJuuor+XFm3tOyHC6iqPNB7Z1ztjGt1eB5WlWQRdd42yF6b8m8JVA9B8Sj96DuUqA5jeTvJtWhLSzMnMPSn5hmzBjsiioTwgpojhqqIA7Npe1QA2borLf77vMtFwYiOOMx5x3C5TEAkjtoucyTnFTLiRnlEccaoDBqAH7fhpB60X71jihjbLqSJLUU2NS8MMS6gvImxfjJypXG31jTLWsma16NHUh7ud4Q2VUt1wNUtxkqsv7V5kiXFUJE282q7dnvV5Kz2W9UFon08Hy+xjCdDaIdyYkNi82Ym04mIOAqEJIvGhJsrtp1VVkexYDLbRMcajZUVWotV2qzQilXGQLDSJ2grtM1+K2HCS14Xr0barJ\/V+RXJLMH7VWp52qr10lwVajtpzcONjjzbeOOK4cJku0q+d3O4c5apYcvBGpOdXU3XdTo0tPWJX5LeS43DK48K++BtE+jbXl2qq8q11e3bdwhql1S+S4GzZhRVZ21dA9SF6b8m8JVA9B8Sj96DuUqA5feRu0tOvLbGh3CVIhlCcJ+K7GUNjhAoduJiWYUx4NnXoAbdz+p9R6f3m2yyx5riwJVwdt1wgqZLGcwU2+dFtVwE0IEJCTbxLilUBg1AVmpVsnmKYF7Fpy1uN83IbfVEA0NUERxXjIlRE5axkk1R5ElSmIKWoLQ9Y7m9HUSSOhKraEqkQCq4oKl8LBNmbjrlbrt8oOsMY\/NGq7b4Huz6juduPC2z3IxLtVts+1XrguI+1WjC7KHS3EwToXbu8PWxt5EupgnxgFsS9lBr1e8vP3My1y5lCy1e7\/cubYGTd7ia4YBmePsrtQE66ola6UpvCsn6mGLZtu7XdAtoNu66gyOXAVQmIIKhtslxEZcBmnFh2qcvDXT23b6PVcz4I2LdmmLNTrqnuKgIXpvybwlUD0HxKP3oO5SoB+gAo0Qf43WlPvC\/9c9VAa9QGC79NZjcLoOnIp5oVtJHJypwHKVO1DlRoVxX5S8lcff39UtCyWZq35VwMrLUN3Pmbc235yJ0xYhxDxJxTNcoNtn75MVWvXbbqcaLqRhCbQTunN2OloOmIlquFriy3hHnJTjrYuKr57XMDVMcEXYmHEldCduM+pI29KeZIa3XbvWjQwsETMm3aGZPYVVSvJbS1\/VE\/XHkYlvp3zav3fa8LTWlQtsK2JDjyBZKIiqrjqmhe8NtPgJxV7xilkZJULrcV+Ya86v1H6ranjRxnyGnHrdPiCTYuKymZxpxsiPAsmJCQrhs4KpTfKAVAQvTfk3hKoHoPiUfvQdylQD9ABHoYvxxtP8A7E\/9c9VAW28PV7WltNPzhUSnO\/QW5lfhPmi5VVPignbFyJWvub6twb48DCc9KqChcZZqpm44rjpkRuOltIzJcxEvKSrjXz0at1ZpGk\/l10Ot0vD2sZ7eMO2kUe0iSYoclUwdewX\/AMQrkH5Sr1K7Gzte5nvZhxCMroGyKgOe1ToPR2o4stLzZ4cx6QwrBynGGykICIuXK8qZxUVXEcF2LQAafl6JR3zaYRSXY7KDMvCuEV5NvXwoA7KAVAQvTfk3hKoHoPiUfvQdylQD9AA9oUvx0tKfeN\/656qAjN48yyXqZJ09emnIc+P9JalLATNvKirJil71xMVyOBwphtTai15XbMbkaSMZxTWJiZ7utS3a+x7PazbkdKcQFliuVGWse3dMC29oO3BMcV2Vzf8Anzi+aNd2WFhp2w27T9jhWW3BzcKA0LLI8aonCRdUiXElXq11IxSVEbKVFQsayKKgESISKK7UXYqUAAmu7A9uz1+o2K\/xJsq2yykQXoh85IikJqoty21Hm0MUVRIcxIqcOHBQBhblNb3bW+7i16juwMNz5ZPg8MUSFr6F82kVBMjVMUDFdvDQHc0BC9N+TeEqgeg+JR+9B3KVAP0ADOgy\/Ha0J95H\/rnqoDT1Fpiwajt62+9wm50VVQxFxFQgNOA2zFUNs04iBUWoCn0Xu4tGkpEx+HMmTSlZRBZ7ovkyCYrkBzKJqir8dSXZw0B1dAKgFQHl1VFo1HhQVVOvhQH567obLA1XvYsNnvIrJhzpjzs0CVfpUaBx9RNeHAyDAuSgP0Et9ut9tiNwrdFZhQ2UwajR2xaaBFXHtQBEFNvUSgJFAQvTfk3hKoHoPiUfvQdylQD9AABrKPedG7y7o2RlBu1tujs6A+qIiqJPq8w+CFsICEk9tKoO2\/yy3qIiY+aNibV6M5t5f9+oD5\/lpvV\/6j+Wc\/f0B5X82u9Xi80fyzn7+gPJfm43qim3zR\/LOfv6AZP83+9ZODzP\/LO\/v6Ag3P8ANvvZm2+TC5y2xkktk0siPHNHQQ0wUm1N0xQuoqitARPyqafn3XfDbJ0dslhWRqRJmPInaijjJstipcGYzc2Jx4L1KAO2gFQEL035N4SqBmJ546KzzfR+byDkzZ8cMEwxwoQe\/vf8N+0oU5bWfqdjH9c\/V7NgvRfO3NZsMdvN8\/twx4cKA5j8BuP1K\/pKA+fgL9yv6OgF+An3J\/o6A8r\/APAOP1I7PQ6A8F\/j1x+o\/Z6FQHgv8deP1F7PQqA7rRnq\/wCal9S\/MfmrOubzRzfMc5gmObo\/a5sOrtoC\/wD73\/DftKAX97\/hv2lARP7p50\/4Of5j5eTJn9nHGhD\/2Q=="}}}';
        $this->assertEquals($jsonString, json_encode($query->toArray()));
    }

    /**
     * @group functional
     */
    public function testFromReference()
    {
        $this->_checkPlugin('image');
        $field = 'image';

        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);

        $type = $index->getType('test');

        $mapping = new Mapping($type, array(
                $field => array(
                    'type' => 'image',
                    'store' => false,
                    'include_in_all' => false,
                    'feature' => array(
                        'CEDD' => array(
                            'hash' => 'BIT_SAMPLING',
                        ),
                    ),
                ),
            )
        );

        $type->setMapping($mapping);

        $type->addDocuments(array(
            new Document(1, array($field => $this->_testFileContent)),
            new Document(2, array($field => $this->_testFileContent)),
            new Document(3, array($field => $this->_testFileContent)),
        ));

        $index->refresh();

        $query = new Image();
        $query->setFieldFeature($field, 'CEDD');
        $query->setFieldHash($field, 'BIT_SAMPLING');
        $query->setFieldBoost($field, 100);
        $query->setImageByReference($field, $index->getName(), $type->getName(), 1);

        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());
    }

    /**
     * @group functional
     */
    public function testFromImage()
    {
        $this->_checkPlugin('image');

        $field = 'image';

        $client = $this->_getClient();
        $index = $client->getIndex('test');
        $index->create(array(), true);

        $type = $index->getType('test');

        $mapping = new Mapping($type, array(
                $field => array(
                    'type' => 'image',
                    'store' => false,
                    'include_in_all' => false,
                    'feature' => array(
                        'CEDD' => array(
                            'hash' => 'BIT_SAMPLING',
                        ),
                    ),
                ),
            )
        );

        $type->setMapping($mapping);

        $type->addDocuments(array(
            new Document(1, array($field => $this->_testFileContent)),
            new Document(2, array($field => $this->_testFileContent)),
            new Document(3, array($field => $this->_testFileContent)),
        ));

        $index->refresh();

        $query = new Image();
        $query->setFieldFeature($field, 'CEDD');
        $query->setFieldHash($field, 'BIT_SAMPLING');
        $query->setFieldBoost($field, 100);
        $query->setFieldImage($field, BASE_PATH.'/data/test.jpg');

        $resultSet = $index->search($query);
        $this->assertEquals(3, $resultSet->count());
    }
}
