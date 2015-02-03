<?php

	$GLOBALS['module'] = 'hangman';
	$GLOBALS['file'] = 'hangview.tpl';

	class HangView
	{
		//
		private $hangman;

		/*
		Constructor that takes a Hangman object
		and calls the appropriate function to 
		display the game.
		*/
		public function __construct(Hangman $hangman)
		{
			$this->hangman = $hangman;
			$this->display();
		}

		/*
		The display function takes the variables from the Hangman
		object and then creates links and pictures to aid in playing
		a hangman game.

		return PHPWS_Template
			Helps display the template.
		*/
		public function display()
		{

			$l = range('a','z');
			$list = $this->hangman->getList();
			
			$tpl['correct'] = $this->hangman->getCorrect();
			$tpl['pic'] = $this->hangman->getPic();

			
			if ($this->hangman->getAction() === 'new_game')
			{
				foreach ($l as $i)
				{
					$link = PHPWS_Text::moduleLink($i,'hangman', array('action'=> 'guess', 'letter'=>$i));
					$tpl['letters'][] = array('LETTER' => $link);
				}		
			}
			else
			{
				foreach ($list as $x)
				{
					$link2 = PHPWS_Text::moduleLink($x,'hangman', array('action'=> 'guess', 'letter'=>$x));
					$tpl['letters'][] = array('LETTER' => $link2);
				}		
			}

			$tpl['wlgame'] = $this->hangman->getWLGame();


			if ($this->hangman->getHideW() === true)
			{
				unset($tpl['letters']); 
			}

				
			$nglink = PHPWS_Text::moduleLink('New Game','hangman', array('action'=> 'new_game'));
			$tpl['ngame'][]= array('NEW_GAME' => $nglink);
	
			return PHPWS_Template::process($tpl, $GLOBALS['module'], $GLOBALS['file']);
		}
	}

?>