<?php

/*
	CVS Revision. 1.1.0
	
	$Log: class.FastTemplate.php,v $
	Revision 1.2  2004/01/06 13:04:16  viva
	fixed bugs that showed when compiling with ZendEncoder
	
	Revision 1.1  2002/10/29 13:06:44  eugen
	no message
	
	Revision 1.1.1.1  2001/09/13 10:54:04  viva
	copied from www.viva.de
	
	Revision 1.22  2001/09/13 10:54:04  hees
	changed the way of replacing the image links. now image links are only replaced
	when the page footer is printed.
	
	Revision 1.21  2001/09/05 15:09:23  eugen
	.
	
	Revision 1.20  2001/08/28 18:08:38  eugen
	Ersetzen von Variablen beschleunigt
	CVSs ----------------------------------------------------------------------
	
	Revision 1.19  2001/08/28 11:33:52  hees
	reworked substitution of image server name to fight against error 500's.
	
	Revision 1.18  2001/08/27 14:25:12  hees
	fixed a bug in class.FastTemplate that could produce an error 500; added an option
	to switch off the image substitution; switched off image substitution while editing
	boxes in admin mode.
	
	Revision 1.17  2001/08/23 13:02:35  hees
	added global src=address substitution for relative paths; redirect to $image_server;
	added $image_server to config.
	
	Revision 1.16  2001/06/19 11:32:47  hees
	added missing code to clear_dynamic for nested dynamic blocks.
	
	Revision 1.15  2001/06/13 15:52:42  hees
	added nested dynamic blocks.
	
	Revision 1.14  2001/06/07 08:57:27  hees
	fixed a bug with variable names that contain a dot; process conditional blocks
	before variables to gain speed.
	
	Revision 1.13  2001/06/05 15:22:40  hees
	fixed a variable replacement bug that occurred ifvariable name contained
	preg meta-characters.
	
	Revision 1.12  2001/06/01 12:10:02  hees
	added feature ELSE CONDITIONAL BLOCK.
	
	Revision 1.11  2001/05/31 11:19:42  hees
	replace unknown variables by empty strings.
	
	Revision 1.10  2001/05/25 15:26:58  eugen
	Fehler beim Parsen von verschachtelten CONDITIONAL BLOCKS in der Funktion parse_template()
	
	Revision 1.9  2001/05/23 11:24:22  hees
	rewrote parse_template to work faster if many variables are defined, bu only a few needed.
	
	Revision 1.8  2001/05/22 14:58:58  hees
	added more diagnostic output if template not found.
	
	Revision 1.7  2001/05/03 09:34:33  hees
	modified to work with preg_ calls to gain a significant speedup.
	
	Revision 1.6  2001/04/24 14:10:17  hees
	fixed a problem with nested DYNAMIC BLOCKsfixed a problem with nested DYNAMIC BLOCKs..
	
	Revision 1.5  2001/04/10 11:54:31  hees
	added new feature for CONDITIONAL BLOCKs, which are only instantiated if  a
	trigger variable is set.
	
	Revision 1.4  2001/04/03 09:12:12  hees
	added revision log.
	
	Revision 1.3  2001/04/03 07:31:30  hees
	extended valid character set for template variable names; fixed line ends to
	natch Mac line ends as well.

	Revision 1.2  2001/03/30 13:52:47  hees
	modified assign to process array values recursively.

	Revision 1.1  2001/03/28 11:33:00  hees
	Initial revision.
	
*/

function substitute_imageserver($a)
{
	global $image_server;
	if (!isset($image_server))
		$image_server = "www.viva.de";
	
	$a = preg_replace ("|^/|", "", $a);	
	if (!preg_match ("/upload/", $a))
		return "http://$image_server/$a";

	return "/$a";
}


function substitute_var($key, $strict)
{
	global $the_vars;
	
	if (isset ($the_vars[$key]))
	{
		$val = $the_vars[$key];
	
		if(gettype($val) != "string")
			settype($val,"string");

		return "$val";
	}
	else
	{
		// variable unknown

		if($strict)
		{
			// Warn about unresolved template variable
			@error_log("[FastTemplate] Warning: no value found for variable: $key ",0);
		}
		return "";
	}
}


class FastTemplate {

	var $FILELIST	=	array();	//	Holds the array of filehandles
									//	FILELIST[HANDLE] == "fileName"

	var $DYNAMIC	=	array();	//	Holds the array of dynamic
									//	blocks, and the fileHandles they
									//	live in.

	var $PARSEVARS	=	array();	//	Holds the array of Variable
									//	handles.
									//	PARSEVARS[HANDLE] == "value"

	var	$LOADED		=	array();	//	We only want to load a template
									//	once - when it's used.
									//	LOADED[FILEHANDLE] == 1 if loaded
									//	undefined if not loaded yet.

	var	$HANDLE		=	array();	//	Holds the handle names assigned
									//	by a call to parse()

	var	$ROOT		=	"";			//	Holds path-to-templates

	var $WIN32		=	false;		//	Set to true if this is a WIN32 server

	var $ERROR		=	"";			//	Holds the last error message

	var $LAST		=	"";			//	Holds the HANDLE to the last
									//	template parsed by parse()

	var $STRICT		=	true;		//	Strict template checking.
									//	Unresolved vars in templates will
									//	generate a warning when found.
									
	var $SUBST_IMAGELINKS =	false;	//	Allow image link substitition.
									
	var $IDCHARS	=	"0-9A-Za-z._";	// These characters form a valid template variable name

//	************************************************************

	function FastTemplate ($pathToTemplates = "")
	{
		global $php_errormsg;

		if(!empty($pathToTemplates))
		{
			$this->set_root($pathToTemplates);
		}

	}	// end (new) FastTemplate ()


//	************************************************************
//	All templates will be loaded from this "root" directory
//	Can be changed in mid-process by re-calling with a new
//	value.

	function set_root ($root)
	{
		$trailer = substr($root,-1);

		if(!$this->WIN32)
		{
			if( (ord($trailer)) != 47 )
			{
				$root = "$root". chr(47);
			}

			if(is_dir($root))
			{
				$this->ROOT = $root;
			}
			else
			{
				$this->ROOT = "";
				$this->error("Specified ROOT dir [$root] is not a directory");
			}
		}
		else
		{
			// WIN32 box - no testing
			if( (ord($trailer)) != 92 )
			{
				$root = "$root" . chr(92);
			}
			$this->ROOT = $root;
		}

	}	// End set_root()


//  **************************************************************
//  Calculates current microtime
//	I throw this into all my classes for benchmarking purposes
//	It's not used by anything in this class and can be removed
//	if you don't need it.


	function utime ()
	{
		$time = explode( " ", microtime());
		$usec = (double)$time[0];
		$sec = (double)$time[1];
		return $sec + $usec;
    }

//  **************************************************************
//	Strict template checking, if true sends warnings to STDOUT when
//	parsing a template with undefined variable references
//	Used for tracking down bugs-n-such. Use no_strict() to disable.

	function strict ()
	{
		$this->STRICT = true;
	}

//	************************************************************
//	Silently discards (removes) undefined variable references
//	found in templates

	function no_strict ()
	{
		$this->STRICT = false;
	}

//  **************************************************************
//	Allow image link substitition.

	function substitute_imagelinks ()
	{
		$this->SUBST_IMAGELINKS = true;
	}

//	************************************************************
//	Disallow image link substitition.

	function no_substitute_imagelinks ()
	{
		$this->SUBST_IMAGELINKS = false;
	}

//	************************************************************
//	A quick check of the template file before reading it.
//	This is -not- a reliable check, mostly due to inconsistencies
//	in the way PHP determines if a file is readable.

	function is_safe ($filename)
	{
		if(!file_exists($filename))
		{
			$this->error("[$filename] does not exist",0);
			return false;
		}
		return true;
	}

//	************************************************************
//	Grabs a template from the root dir and 
//	reads it into a (potentially REALLY) big string

	function get_template ($template)
	{
		if(empty($this->ROOT))
		{
			$this->error("Cannot open template. Root not valid.",1);
			return false;
		}

		$basename	=	$this->FILELIST["$template"];
		$filename	=	"$this->ROOT"."$basename";

		$contents = implode("",(@file($filename)));
		if( (!$contents) or (empty($contents)) )
		{
			$this->error("get_template() failure: [$template => $filename] $php_errormsg",1);
		}

		return $contents;

	} // end get_template

//	************************************************************
//	This routine get's called by parse() and does the actual
//	{VAR} to VALUE conversion within the template.

	function parse_template ($template, $tpl_array)
	{
		global $the_template;
		$the_template =& $this;

		if (preg_match_all("/<!-- BEGIN CONDITIONAL BLOCK: ([".$this->IDCHARS."]+) -->/ms", $template, $m))
		{
			while (list ($k,$key) = each($m[1]))
			{
				$qkey = preg_quote ($key);
				$val = $tpl_array[$key];

				if ($val!="")
				{
					$template = preg_replace("/<!-- BEGIN CONDITIONAL BLOCK: $qkey -->(.*)(<!-- ELSE CONDITIONAL BLOCK: $qkey -->.*)?<!-- END CONDITIONAL BLOCK: $qkey -->/Us", "\\1", "$template");
				}
				else
				{
					$template = preg_replace("/<!-- BEGIN CONDITIONAL BLOCK: $qkey -->.*(<!-- ELSE CONDITIONAL BLOCK: $qkey -->(.*))?<!-- END CONDITIONAL BLOCK: $qkey -->/Us", "\\2", "$template");
				}
			}
		}
	
		global $the_vars;
		$the_vars = $tpl_array;
		$template = preg_replace("/\{([".$this->IDCHARS."]+)\}/e", "substitute_var('\\1', ".($this->STRICT ? "true" : "false").")", "$template");
		
		return $template;

	}	// end parse_template();

//	************************************************************
//	The meat of the whole class. The magic happens here.

	function parse ( $ReturnVar, $FileTags )
	{
		$time = $this->utime();
		
		$append = false;
		$this->LAST = $ReturnVar;
		$this->HANDLE[$ReturnVar] = 1;

		if (gettype($FileTags) == "array")
		{
			unset($this->$ReturnVar);	// Clear any previous data

			while ( list ( $key , $val ) = each ( $FileTags ) )
			{
				if ( (!isset($this->$val)) || (empty($this->$val)) )
				{
					if(isset($this->DYNAMIC["$val"]))	$this->$val = $this->parse_dynamic($val,$ReturnVar);
						else							$this->$val = $this->get_template($val);
					$this->LOADED["$val"] = 1;
				}

				//	Array context implies overwrite

				$this->$ReturnVar = $this->parse_template($this->$val,$this->PARSEVARS);

				//	For recursive calls.

				$this->assign( array( $ReturnVar => $this->$ReturnVar ) );

			}
		}	// end if FileTags is array()
		else
		{
			// FileTags is not an array

			$val = $FileTags;

			if( (substr($val,0,1)) == '.' )
			{
				// Append this template to a previous ReturnVar

				$append = true;
				$val = substr($val,1);
			}

			if ( (!isset($this->$val)) || (empty($this->$val)) )
			{
				if(isset($this->DYNAMIC["$val"]))	$this->$val = $this->parse_dynamic($val,$ReturnVar);
					else							$this->$val = $this->get_template($val);
			}

			if($append)
			{
				$this->$ReturnVar .= $this->parse_template($this->$val,$this->PARSEVARS);
			}
			else
			{
				$this->$ReturnVar = $this->parse_template($this->$val,$this->PARSEVARS);
			}

			//	For recursive calls.

			$this->assign(array( $ReturnVar => $this->$ReturnVar) );

		}
		//printf ("<!-- template parse time: %.3f sec. -->\n", $this->utime() - $time);
	}	//	End parse()


//	************************************************************

	function fetch ( $template = "" )
	{
		if(empty($template))
		{
			$template = $this->LAST;
		}
		if( (!(isset($this->$template))) || (empty($this->$template)) )
		{
			$this->error("Nothing parsed, nothing printed",0);
			return "";
		}

		if ($this->SUBST_IMAGELINKS)	$template = preg_replace("/(background|src|href)=\"((?!http)[^\"]+\.(jpg|gif|js|css))\"/Uei", "'\\1=\"'.substitute_imageserver('\\2').'\"'", $this->$template);
			else						$template =& $this->$template;
		
		return($template);
	}


//	************************************************************

	function FastPrint ( $template = "" )
	{
		print ($this->fetch ($template));
	}

//	************************************************************

	function define_dynamic ($Macro, $ParentName)
	{
		//	A dynamic block lives inside another template file.
		//	It will be stripped from the template when parsed
		//	and replaced with the {$Tag}.

		$this->DYNAMIC["$Macro"] = $ParentName;
		return true;
	}

//	************************************************************

	function parse_dynamic ($Macro,$MacroName)
	{
		// The file must already be in memory.

		$ParentTag = $this->DYNAMIC["$Macro"];
		while (isset ($this->DYNAMIC[$ParentTag]) && !$this->LOADED[$ParentTag])
			$ParentTag = $this->DYNAMIC[$ParentTag];
		
		if( (!$this->$ParentTag) or (empty($this->$ParentTag)) )
		{
			$this->$ParentTag = $this->get_template($ParentTag);
			$this->LOADED[$ParentTag] = 1;
		}
		
		if($this->$ParentTag)
		{
			$reg = "/<!-- BEGIN DYNAMIC BLOCK: $Macro -->(.*)<!-- END DYNAMIC BLOCK: $Macro -->/sm";
			$tpl = $this->$ParentTag;
			if (preg_match ($reg, $tpl, $m))
			{
				$this->$ParentTag = preg_replace ($reg, "{".$MacroName."}", $tpl);
				return $m[1];
			}
		}	// $ParentTag NOT loaded - MAJOR oopsie
		else
		{
			@error_log("ParentTag: [$ParentTag] not loaded!",0);
			$this->error("ParentTag: [$ParentTag] not loaded!",0);
		}
		return "";
	}

//	************************************************************
//	Strips a DYNAMIC BLOCK from a template.

	function clear_dynamic ($Macro="")
	{
		if(empty($Macro)) { return false; }

		// The file must already be in memory.

		$ParentTag = $this->DYNAMIC["$Macro"];
		while (isset ($this->DYNAMIC[$ParentTag]) && !$this->LOADED[$ParentTag])
			$ParentTag = $this->DYNAMIC[$ParentTag];

		if( (!$this->$ParentTag) or (empty($this->$ParentTag)) )
		{
			$this->$ParentTag = $this->get_template($ParentTag);
			$this->LOADED[$ParentTag] = 1;
		}

		if($this->$ParentTag)
		{
			$reg = "/<!-- BEGIN DYNAMIC BLOCK: $Macro -->(.*)<!-- END DYNAMIC BLOCK: $Macro -->/sm";
			$tpl = $this->$ParentTag;
			if (preg_match ($reg, $tpl, $m))
			{
				$this->$ParentTag = preg_replace ($reg, "", $tpl);
				return true;
			}
		}	// $ParentTag NOT loaded - MAJOR oopsie
		else
		{
			@error_log("ParentTag: [$ParentTag] not loaded!",0);
			$this->error("ParentTag: [$ParentTag] not loaded!",0);
		}
		return false;
	}


//	************************************************************

	function define ($fileList)
	{
		while ( list ($FileTag,$FileName) = each ($fileList) )
		{
			$this->FILELIST["$FileTag"] = $FileName;
		}
		return true;
	}

//	************************************************************

	function clear_parse ( $ReturnVar = "")
	{
		$this->clear($ReturnVar);
	}

//	************************************************************

	function clear ( $ReturnVar = "" )
	{
		// Clears out hash created by call to parse()

		if(!empty($ReturnVar))
		{
			if( (gettype($ReturnVar)) != "array")
			{
				unset($this->$ReturnVar);
				return;
			}
			else
			{
				while ( list ($key,$val) = each ($ReturnVar) )
				{
					unset($this->$val);
				}
				return;
			}
		}

		// Empty - clear all of them

		while ( list ( $key,$val) = each ($this->HANDLE) )
		{
			$KEY = $key;
			unset($this->$KEY);
		}
		return;

	}	//	end clear()

//	************************************************************

	function clear_all ()
	{
		$this->clear();
		$this->clear_assign();
		$this->clear_define();
		$this->clear_tpl();

		return;

	}	//	end clear_all

//	************************************************************

	function clear_tpl ($fileHandle = "")
	{
		if(empty($this->LOADED))
		{
			// Nothing loaded, nothing to clear

			return true;
		}
		if(empty($fileHandle))
		{
			// Clear ALL fileHandles

			while ( list ($key, $val) = each ($this->LOADED) )
			{
				unset($this->$key);
			}
			unset($this->LOADED);

			return true;
		}
		else
		{
			if( (gettype($fileHandle)) != "array")
			{
				if( (isset($this->$fileHandle)) || (!empty($this->$fileHandle)) )
				{
					unset($this->LOADED[$fileHandle]);
					unset($this->$fileHandle);
					return true;
				}
			}
			else
			{
				while ( list ($Key, $Val) = each ($fileHandle) )
				{
					unset($this->LOADED[$Key]);
					unset($this->$Key);
				}
				return true;
			}
		}

		return false;

	}	// end clear_tpl

//	************************************************************

	function clear_define ( $FileTag = "" )
	{
		if(empty($FileTag))
		{
			unset($this->FILELIST);
			return;
		}

		if( (gettype($Files)) != "array")
		{
			unset($this->FILELIST[$FileTag]);
			return;
		}
		else
		{
			while ( list ( $Tag, $Val) = each ($FileTag) )
			{
				unset($this->FILELIST[$Tag]);
			}
			return;
		}
	}

//	************************************************************
//	Aliased function - used for compatibility with CGI::FastTemplate
//	function clear_parse ()
//	{
//		$this->clear_assign();
//	}

//	************************************************************
//	Clears all variables set by assign()

	function clear_assign ()
	{
		if(!(empty($this->PARSEVARS)))
		{
			while(list($Ref,$Val) = each ($this->PARSEVARS) )
			{
				unset($this->PARSEVARS["$Ref"]);
			}
		}
	}

//	************************************************************

	function clear_href ($href)
	{
		if(!empty($href))
		{
			if( (gettype($href)) != "array")
			{
				unset($this->PARSEVARS[$href]);
				return;
			}
			else
			{
				while (list ($Ref,$val) = each ($href) )
				{
					unset($this->PARSEVARS[$Ref]);
				}
				return;
			}
		}
		else
		{
			// Empty - clear them all

			$this->clear_assign();
		}
		return;
	}

//	************************************************************

	function assign ($tpl_array, $trailer="")
	{
		if(gettype($tpl_array) == "array")
		{
			while ( list ($key,$val) = each ($tpl_array) )
			{
				if (!(empty($key)))
				{
					//	Empty values are allowed
					//	Empty Keys are NOT

					if (!is_array($val))	$this->PARSEVARS["$key"] = $val;
						else				while (list($k,$v) = each($val))
												$this->assign("$key.$k",$v);
				}
			}
		}
		else
		{
			// Empty values are allowed in non-array context now.
			if (!empty($tpl_array))
			{
					if (!is_array($trailer))	$this->PARSEVARS["$tpl_array"] = $trailer;
						else					while (list($k,$v) = each($trailer))
													$this->assign("$tpl_array.$k",$v);
			}
		}
	}

//	************************************************************
//	Return the value of an assigned variable.
//	Christian Brandel cbrandel@gmx.de

	function get_assigned($tpl_name = "")
	{
		if(empty($tpl_name)) { return false; }
		if(isset($this->PARSEVARS["$tpl_name"]))
		{
			return ($this->PARSEVARS["$tpl_name"]);
		}
		else
		{
			return false;
        }
	}

//	************************************************************

	function error ($errorMsg, $die = 0)
	{
		$this->ERROR = $errorMsg;
		echo "ERROR: $this->ERROR <BR> \n";
		if ($die == 1)
		{
			exit;
		}

		return;

	} // end error()


//	************************************************************



//	************************************************************

} // End class.FastTemplate.php3

?>
