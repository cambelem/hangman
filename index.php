<?php
/**
 * @author Eric Cambel
 */

	//Create a new Hangman class
	$hangman = new Hangman();
		
	//Setup the view class using a Hangman object
	$view = new HangView($hangman);

	//Render view
	Layout::add($view->display(), 'hangman');

?>
