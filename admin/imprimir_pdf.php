<?php
	function imprimir_PDF()
	{
		// get page title
	    $pageTitle = $page->evaluate('document.title')->getReturnValue();

    	// screenshot - Say "Cheese"! 😄
    	$page->screenshot()->saveToFile('/foo/bar.png');

    	// pdf
    	$page->pdf(['printBackground' => false])->saveToFile('/foo/bar.pdf');
	}
?>