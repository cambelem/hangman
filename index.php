<?php
/**
 * @author Eric Cambel
 */

	//Create a new Hangman class.
	$hangman = new Hangman();
		
	//Setup the view class using a Hangman object.
	$view = new HangView($hangman);

	//Render the view class.
	Layout::add($view->display(), 'hangman');

?>
