<?php

namespace Simplex\Tests;
use Pager;
use PHPUnit\Framework\TestCase;

class PagerTests extends TestCase
{

    public function testTestme()
    {
        $this->assertEquals("hello", "hello");
    }

    public function testPagerDefaultsToCurrentPageOne() {
        $page = new Pager(10,50);
        $this->assertEquals(1, $page->getCurrentPage());
    }

    public function testNumberOfPages() {
        $page = new Pager(10,50);
        $this->assertEquals(5, $page->getNumberOfPages());
    }

    public function testNumberOfPagesFraction() {
        $page = new Pager(10,51);
        $this->assertEquals(6, $page->getNumberOfPages());
    }

    public function testNumberOfPagesDown() {
        $page = new Pager(10,49);
        $this->assertEquals(5, $page->getNumberOfPages());
    }

    public function testPageTwoStartIndex() {
        $page = new Pager(10,50);
        $page->setCurrentPage(2);
        $this->assertEquals(10,$page->getStartIndex());
    }

    public function testPageTwoEndIndex() {
        $page = new Pager(10,50);
        $page->setCurrentPage(2);
        $this->assertEquals(20,$page->getEndIndex());
    }

    public function testPageOfTheEndFails() {
        $page = new Pager(10,50);
        try {
            $page->setCurrentPage(6);
        } catch(\Exception $e) {
            $this->assertEquals("Page is passed the number of pages expected", $e->getMessage());
            return;
        }

        $this->fail();
    }

    public function testHasPrevious() {
        $page = new Pager(10,50);
        $page->setCurrentPage(2);
        $this->assertTrue($page->hasPrevious());
        $page->setCurrentPage(1);
        $this->assertFalse($page->hasPrevious());
    }

    public function testHasNext() {
        $page = new Pager(10,50);
        $page->setCurrentPage(1);
        $this->assertTrue($page->hasNext());
        $page->setCurrentPage(4);
        $this->assertTrue($page->hasNext());
        $page->setCurrentPage(5);
        $this->assertFalse($page->hasNext());

        $page = new Pager(10,0);
        $this->assertFalse($page->hasNext());

    }

}


