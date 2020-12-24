<?php

declare(strict_types=1);

trait HtmlAssertions {
	protected function assertHTMLEquals( $expected, $actual ) {
		$expected_dom = new DomDocument();
		$actual_dom   = new DomDocument();

		$this->assertNotEmpty( $actual );
		
		$expected_dom->loadXML( '<test>' . $expected . '</test>' );
		$actual_dom->loadXML( '<test>' . $actual . '</test>' );

		$this->assertEqualXMLStructure( $expected_dom->firstChild, $actual_dom->firstChild, true, $actual );
	}
}
