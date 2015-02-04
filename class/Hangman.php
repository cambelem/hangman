<?php

	class Hangman 
	{		
		
		private $gletter; 
		private $word; 
		private $cletters; 
		private $isletter; 
		private $count;
		private $list; 		
		private $wlgame; 		
		private $action; 
		
		
		/*
		This constructor determines the action
		and appropriately calls the functions to
		store info to play a hangman game.
		*/
		public function __construct()
		{
			$this->list = array();
			$this->hidew = false;

			if (!isset($_GET['action']))
			{
				$_GET['action'] = 'new_game';
			}

			$this->action = $_GET['action'];	
			$this->cletters = array();

			if (isset($_SESSION['correctletters']))
			{
				$this->cletters = $_SESSION['correctletters'];
			}

			switch ($this->getAction())
			{				
				case 'new_game':
					
					$this->correct = $this->ranWord();					

					break;

				case 'guess':

					$this->word = $_SESSION['word'];
					$this->gletter = $_GET['letter'];
					$this->isletter = $this->findingLetters($this->gletter, $this->word);	
					$this->list = $_SESSION['unUsedLetters'];					
																								
					break;

				default:

					if (!isset($_SESSION['word']))
					{
						$this->ranWord();
					}
					
			}
			
			if (isset($_SESSION['correctletters']))
			{
				$this->cletters = $_SESSION['correctletters'];
			}

			$this->wlgame = $this->checkWinOrLoss($this->cletters , $this->word);
		}


		/*
			Accessors that grab the variables needed to 
			run the Hangman game.

			return all field variables.
		*/
		public function getGLetter()
		{
			return $this->gletter;
		}

		public function getList()
		{
			return $this->list;
		}

		public function getWord() 
		{			
			return $this->word;
		}

		public function getAction()
		{
			return $this->action;
		}

		public function getCLetters() 
		{ 
			return $this->cletters;
		}

		public function getIsLetter()
		{
			return $this->isletter;
		}

		public function getWLGame() 
		{
			return $this->wlgame;
		}

		public function getCount()
		{
			return $this->count;
		}

		/*
		This function grabs a new random word and resets
		the SESSION variables and field variables 
		back to default settings.
		
		return $tempArray which holds an array of '_'.			
		*/
		private function ranWord()
		{	
			$tempArray = array();
			$maxAttempts = 0;
			$_SESSION['attempts'] = ($maxAttempts);
			$this->count = $_SESSION['attempts'];
			$_SESSION['count'] = 0;
			$_SESSION['c'] = 0;
			$this->cletters = array();
			unset($_SESSION['correctletters']);
			unset($_SESSION['unUsedLetters']);			
	
			$myfile = file_get_contents(PHPWS_SOURCE_DIR . 'mod/hangman/hangwords.txt');
			
			$words = preg_split('/[\s]+/', $myfile);
			$random = rand(0,count($words));
			$_SESSION['word'] = (strtolower($words[$random]));
			$this->word = $_SESSION['word'];

			$answer = str_split($_SESSION['word']);
			for ($i = 0; $i < count($answer); $i++)
			{						
				$tempArray[$i] = array('CORRECTLETTERS' => "_ ");
			}

			return $tempArray;
		}

		/*
		This function grabs the guess and the word, 
		splits the word, and then compares the guess
		to each character.

		return $inWord that holds a boolean value.
		*/
		private function findingLetters($guess, $word)
		{
			$letters = range('a','z');
			$inWord = false;
			$usedLetters = array();
			$answer = str_split($word);

			for ($i = 0; $i < count($answer); $i++)
			{
				if ($guess == $answer[$i])
				{
					$inWord = true;
					$_SESSION['correctletters'][] = $guess;
					$usedLetters[$_SESSION['count']] = $guess;
				}

			}

			$_SESSION['count'] = $_SESSION['count'] + 1;
			
			if ($inWord == false)
			{
				$_SESSION['attempts'] = $_SESSION['attempts'] + 1;
			}
			
			$this->count = $_SESSION['attempts'];

			if (!isset($_SESSION['unUsedLetters']))
			{
				$unUsedLetters = $letters;
			}
			else
			{
				$unUsedLetters = $_SESSION['unUsedLetters'];
			}

			foreach ($unUsedLetters as &$letter)
			{
				if ($letter == $guess)
				{
					$letter = 0;			
				}
			}
			$_SESSION['unUsedLetters'] = $unUsedLetters;
		
			return $inWord;
		}

		/*
			listLetters displays a list of used or
			unused letters for the user to click on.

			return $list that holds a list of letters.
		*/
		public function listLetters()
		{
			$l = array();

			if($this->action === 'new_game')
			{
				$l = range('a','z');
			}
			else
			{
				$l = $this->list;
			}


			foreach ($l as $value)
			{
				$link = PHPWS_Text::moduleLink($value,'hangman', array('action'=> 'guess', 'letter'=>$value));
				$list[] = array('LETTER' => $link);
			}	

			return $list;
		}

		/*
		This function compares the guess
		and the word to determine if the player wins 
		or lose and displays the correct response.

		return $winloserarray that holds a string
		determining if the player won or lost.
		*/
		private function checkWinOrLoss($correctarray,$word)
		{
			$phrase = '<font color="red">';
			if ($_SESSION['attempts'] == 6)
			{				 
				$winlosearray[]= array('WIN_LOSS' => "You $phrase lost</font>! <br> The word was $word.");	
				return $winlosearray;
			}
			
			if (count($correctarray) == count(str_split($word)))
			{				 
				$winlosearray[]= array('WIN_LOSS' => "You win!");
				return $winlosearray;						
			}		
		}
	}
?>
