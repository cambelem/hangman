<?php

	class HangView
	{
		//Holds a hangman object as a reference variable.
		private $hangman;
		const MODULE = 'hangman';
		const FILE = 'hangview.tpl';


		/*
		Constructor that sets the Hangman object
		to the field variable.
		*/
		public function __construct(Hangman $hangman)
		{
			$this->hangman = $hangman;
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
			$gletter = $this->hangman->getGLetter();
			$word = $this->hangman->getWord();
			$cletters = $this->hangman->getCletters();
			$isletter = $this->hangman->getIsLetter();
			$count = $this->hangman->getCount();
			$list = $this->hangman->getList();		

			
			$tpl['correct'] = $this->displayLetters($gletter, $word, $cletters);
			$tpl['pic'] = $this->picture($count);
			$tpl['letters'] = $this->hangman->listLetters();			
			$tpl['wlgame'] = $this->hangman->getWLGame();

			$hidew = $this->hideWords($cletters , $word);
			if ($hidew === true)
			{
				unset($tpl['letters']); 
			}

	
			$nglink = PHPWS_Text::moduleLink('New Game','hangman', array('action'=> 'new_game'));
			$tpl['ngame'][]= array('NEW_GAME' => $nglink);
	
			return PHPWS_Template::process($tpl, self::MODULE, self::FILE);
		}

		/*
		This function determines which picture to
		choose from if the user guesses wrong.

		return $picture which holds the current
		hangman picture.
		*/
		private function picture($count)
		{

			$pic = 'mod/hangman/img/' . 'hang' . $count . ".gif";   
			$picture[] = array('PICTURE' => "<img src=" . $pic . " />");
			
			return $picture;
		}

		/*
		This function displays the letters on the screen
		for the users to choose from.

		return $correctarray that holds an array
		of letters or symbols.
		*/	
		private function displayLetters($gletter, $word, $cletters)
		{
			$answer = str_split($word);
			$correctarray = array();

			for ($i = 0; $i < count($answer); $i++)
			{
				if ($gletter == $answer[$i])
				{
					$correctarray[] = array('CORRECTLETTERS' => $gletter);
				}
				elseif (in_array($answer[$i], $cletters))
				{
					$correctarray[] = array('CORRECTLETTERS' => $answer[$i]);
				}				
				else
				{
					$correctarray[] = array('CORRECTLETTERS' => "_ ");					
				}
			}
			return $correctarray;
		}

		/*
		This function hides the letters if the player
		wins or loses the game.

		return true or false if the player wins or loses
		the game.
		*/
		private function hideWords($correctarray,$word)
		{
			if ($_SESSION['attempts'] == 6)
			{
				return true;
			}
			
			if (count($correctarray) == count(str_split($word)))
			{
				return true;
			}

			return false;
		}
	}

?>