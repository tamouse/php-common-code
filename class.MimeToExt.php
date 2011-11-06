<?php 
/**
 * Class for translateing mimetypes to common file extensions
 *
 * @author Tamara Temple tamara@tamaratemple.com
 * @version 2011-06-26
 * @copyright Tamara Temple Development, 16 June, 2011
 * @package commoncode
 **/

/**
 * Define DocBlock
 **/

// ===============
// = ERROR CODES =
// ===============
/**
 * E_NOAPPEND - error code thrown when there is no append method for the given property
 */
define('E_NOAPPEND', 1);
/**
 * E_NOTARRAY - error code thrown when the value passed in to set_mimetoext is not an array
 */
define('E_NOTARRAY', 2);
/**
 * E_INVALIDEMIMETYPE - error code thrown when the caller attempts to add an invalide mime type to the mimetoext array
 */
define('E_INVALIDEMIMETYPE', 3);
/**
 * E_NOTASTRING - error code thrown when the caller creates a new object or sets the filename property with something other than a string
 */
define('E_NOTASTRING', 4);
/**
 * E_NOFILE - error code thrown when the file referred to by filename does not exist
 * 
 * This error code won't be thrown at construction or setting. The caller may have set up the object prior to creating the file.
 * Instead, this will be thrown when the file is examined with method ext or type
 */
define('E_NOFILE', 5);
/**
 * E_NOFILENAME - error code thrown with the filename property is not set
 */
define('E_NOFILENAME', 6);
/**
 * E_UNKNOWNMETHOD - error code thrown in __call when it hits a method it doesn't recognize
 */
define('E_UNKNOWNMETHOD', '7');





/**
 * Class for dealing with file's mimetypes
 *
 * @package commoncode
 * @author Tamara Temple
 **/
class MimeToExt
{

	/**
	 * filename - name of file to work with, set in constructor, may be reset by accessors
	 *
	 * @var string
	 **/
	private $filename;

	/**
	 * file info object created during finfo construction
	 *
	 * @var object
	 **/
	private $finfo;

	/**
	 * flag to determine if we're using finfo class
	 * 
	 * @var boolean
	 */
	private $use_finfo = TRUE;

	
	/**
	 * class constructor
	 *
	 * @return object of type MimeToExt
	 * @author Tamara Temple
	 **/
	public function __construct($fn=null)
	{
		if (isset($fn) && !is_string($fn)) throw new Exception("\$fn is not a string in ".get_class().'::__construct', E_NOTASTRING);
		
		if (isset($fn)) $this->filename = $fn;
		//error_log('filename: '.$this->filename.PHP_EOL,3,'testlog.log');
		
		$this->mimetoext  = self::$default_mimetoext;
		//error_log('default_mimetoext: '.print_r(self::$default_mimetoext,true).PHP_EOL,3,'testlog.log');
		//error_log('mimetoext: '.print_r($this->mimetoext,true).PHP_EOL,3,'testlog.log');

		$this->get_finfo();
		if (!$this->finfo) $this->use_finfo = FALSE;
		
		error_log(($this->use_finfo?'':'NOT ').'Using finfo'.PHP_EOL,3,'testlog.log');
	}

	/**
	 * pseudo method calls
	 * 
	 * __call acts as a dispatcher for psuedo methods, i.e. methods that aren't directly declared as public functions
	 * 
	 * @method string get_fn() - returns current setting of $filename
	 * @method null set_fn() - set $filename
	 * @method bool isset_fn() - TRUE if $filename is set, FALSE otherwise
	 * @method bool using_finfo_p() - TRUE if using finfo class, FALSE if using external program
	 * @method string type() - returns mime type of $filename
	 * @method string ext() - returns extension associated with $filename's mime type (note, may be different from $filename's actual extension)
	 * @param string $method - name of method called via MimeToExt object
	 * @param array $params - parameters of pseudo method call
	 * @author Tamara Temple 
	 * @package commoncode
	 */
	public function __call($method, $params)
	{
		switch ($method) {
			case 'get_fn':
				return $this->filename;
				break;
			case 'set_fn':
				$this->filename = $params[0];
				return;
				break;
			case 'isset_fn':
				return isset($this->filename);
				break;
			case 'using_finfo_p':
				return $this->use_finfo;
				break;
			case 'type':
				return $this->get_mimetype();
				break;
			case 'ext':
				$type = $this->get_mimetype();
				return isset($mimetoext[$type])?$mimetoext[$type]:'dat';
				break;
			default:
				throw new Exception("Unknown method", E_UNKNOWNMETHOD);
				break;
		}
	}
	
	
	/**
	 * If the finfo class exists, retrieve an object for our use
	 *
	 * @return object|boolean
	 * @author Tamara Temple
	 **/
	private function get_finfo()
	{
		if (class_exists('finfo', true)) { // check to see if finfo class exists, and if it does, autoload it
			$f = new finfo(FILEINFO_MIME+FILEINFO_SYMLINKS);
			if (FALSE === $f) {
				error_log('Could not initialize finfo on first try'.PHP_EOL,3,'testlog.log');
				// the default didn't work, try another
				$f = new finfo(FILEINFO_MIME+FILEINFO_SYMLINKS,ALTMIMEDBLOCATION);
				if (FALSE === $f) {
					error_log('Could not initialize finfo on second try!'.PHP_EOL,3,'testlog.log');
					$this->use_finfo = FALSE;
					return FALSE;
				} 
			}
			$this->finfo = $f;
			//error_log('finfo:'.print_r($this->finfo,true).PHP_EOL,3,'testlog.log');
			return;
		} else {
			//error_log('Class finfo does not exist in this version of PHP'.PHP_EOL,3,'testlog.log');
			$this->use_finfo = FALSE;
		}
		return;
	}
	
	/**
	 * Get the file $this-filename's mimetype
	 *
	 * @return string 
	 * @author Tamara Temple <tamara@tamaratemple.com>
	 **/
	private function get_mimetype()
	{
		if (!isset($this->filename)) throw new Exception("filename is not set", E_NOFILENAME);
		if (!file_exists($this->filename)) throw new Exception("$this->filename does not exist", E_NOFILE);

		if ($use_finfo) {
			$type = $finfo->file($this->filename,FILEINFO_MIME+FILEINFO_SYMLINKS);
		} else {
			$cmd = "file -b -I ".escapeshellcmd($fn)." 2>/dev/null";
			$type = rtrim(`$cmd`);
		}
		return $type;
	}
	
	
	/**
	 * Static private variable with default mime type to file extension mappings
	 *
	 * @var array
	 **/
	private static $default_mimetoext = array(
		
		'application/andrew-inset' => '',
		'application/annodex' => '',
		'application/atom+xml' => '',
		'application/dicom' => '',
		'application/docbook+xml' => '',
		'application/ecmascript' => '',
		'application/epub+zip' => '',
		'application/gnunet-directory' => '',
		'application/illustrator' => 'ai',
		'application/javascript' => 'js',
		'application/mac-binhex40' => '',
		'application/mathematica' => '',
		'application/mathml+xml' => '',
		'application/mbox' => 'mbox',
		'application/metalink+xml' => '',
		'application/msword' => 'doc',
		'application/msword-template' => 'dot',
		'application/mxf' => '',
		'application/octet-stream' => 'dat',
		'application/oda' => 'oda',
		'application/ogg' => 'ogg',
		'application/oxps' => 'oxps',
		'application/pdf' => 'pdf',
		'application/pgp-encrypted' => '',
		'application/pgp-keys' => '',
		'application/pgp-signature' => '',
		'application/pkcs10' => '',
		'application/pkcs7-mime' => '',
		'application/pkcs7-signature' => '',
		'application/pkcs8' => '',
		'application/pkix-cert' => '',
		'application/pkix-crl' => '',
		'application/pkix-pkipath' => '',
		'application/postscript' => 'ps',
		'application/prs.plucker' => '',
		'application/ram' => '',
		'application/rdf+xml' => '',
		'application/relax-ng-compact-syntax' => '',
		'application/rss+xml' => '',
		'application/rtf' => 'rtf',
		'application/sdp' => 'sdp',
		'application/sieve' => '',
		'application/smil' => '',
		'application/vnd.apple.mpegurl' => '',
		'application/vnd.corel-draw' => '',
		'application/vnd.emusic-emusic_package' => '',
		'application/vnd.google-earth.kml+xml' => 'kml',
		'application/vnd.google-earth.kmz' => '',
		'application/vnd.hp-hpgl' => '',
		'application/vnd.hp-pcl' => '',
		'application/vnd.iccprofile' => '',
		'application/vnd.lotus-1-2-3' => '',
		'application/vnd.mozilla.xul+xml' => '',
		'application/vnd.ms-access' => '',
		'application/vnd.ms-cab-compressed' => '',
		'application/vnd.ms-excel' => '.xls',
		'application/vnd.ms-powerpoint' => 'ppt',
		'application/vnd.ms-tnef' => '',
		'application/vnd.ms-works' => 'wks',
		'application/vnd.ms-wpl' => '',
		'application/vnd.oasis.opendocument.chart' => '',
		'application/vnd.oasis.opendocument.chart-template' => '',
		'application/vnd.oasis.opendocument.database' => '',
		'application/vnd.oasis.opendocument.formula' => '',
		'application/vnd.oasis.opendocument.formula-template' => '',
		'application/vnd.oasis.opendocument.graphics' => '',
		'application/vnd.oasis.opendocument.graphics-flat-xml' => '',
		'application/vnd.oasis.opendocument.graphics-template' => '',
		'application/vnd.oasis.opendocument.image' => '',
		'application/vnd.oasis.opendocument.presentation' => '',
		'application/vnd.oasis.opendocument.presentation-flat-xml' => '',
		'application/vnd.oasis.opendocument.presentation-template' => '',
		'application/vnd.oasis.opendocument.spreadsheet' => '',
		'application/vnd.oasis.opendocument.spreadsheet-flat-xml' => '',
		'application/vnd.oasis.opendocument.spreadsheet-template' => '',
		'application/vnd.oasis.opendocument.text' => '',
		'application/vnd.oasis.opendocument.text-flat-xml' => '',
		'application/vnd.oasis.opendocument.text-master' => '',
		'application/vnd.oasis.opendocument.text-template' => '',
		'application/vnd.oasis.opendocument.text-web' => '',
		'application/vnd.openofficeorg.extension' => '',
		'application/vnd.openxmlformats-officedocument.presentationml.presentation' => '',
		'application/vnd.openxmlformats-officedocument.presentationml.slideshow' => '',
		'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => '',
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => '',
		'application/vnd.rn-realmedia' => '',
		'application/vnd.stardivision.calc' => '',
		'application/vnd.stardivision.chart' => '',
		'application/vnd.stardivision.draw' => '',
		'application/vnd.stardivision.impress' => '',
		'application/vnd.stardivision.mail' => '',
		'application/vnd.stardivision.math' => '',
		'application/vnd.stardivision.writer' => '',
		'application/vnd.sun.xml.calc' => '',
		'application/vnd.sun.xml.calc.template' => '',
		'application/vnd.sun.xml.draw' => '',
		'application/vnd.sun.xml.draw.template' => '',
		'application/vnd.sun.xml.impress' => '',
		'application/vnd.sun.xml.impress.template' => '',
		'application/vnd.sun.xml.math' => '',
		'application/vnd.sun.xml.writer' => '',
		'application/vnd.sun.xml.writer.global' => '',
		'application/vnd.sun.xml.writer.template' => '',
		'application/vnd.symbian.install' => '',
		'application/vnd.wordperfect' => 'wp',
		'application/x-7z-compressed' => '7z',
		'application/x-abiword' => 'abi',
		'application/x-ace' => 'ace',
		'application/x-alz' => 'alz',
		'application/x-amipro' => 'ami',
		'application/x-aportisdoc' => '',
		'application/x-apple-diskimage' => 'dmg',
		'application/x-applix-spreadsheet' => '',
		'application/x-applix-word' => '',
		'application/x-arc' => '',
		'application/x-archive' => 'ar',
		'application/x-arj' => 'arj',
		'application/x-asp' => 'asp',
		'application/x-awk' => 'awk',
		'application/x-bcpio' => 'cpio',
		'application/x-bittorrent' => 'torrent',
		'application/x-blender' => '',
		'application/x-bzdvi' => '',
		'application/x-bzip' => 'bz2',
		'application/x-bzip-compressed-tar' => 'tbz',
		'application/x-bzpdf' => '',
		'application/x-bzpostscript' => '',
		'application/x-cb7' => 'cb7',
		'application/x-cbr' => 'cbr',
		'application/x-cbt' => 'cbt',
		'application/x-cbz' => 'cbz',
		'application/x-cd-image' => '',
		'application/x-cdrdao-toc' => '',
		'application/x-chess-pgn' => '',
		'application/x-chm' => 'chm',
		'application/x-cisco-vpn-settings' => '',
		'application/x-class-file' => '',
		'application/x-compress' => 'Z',
		'application/x-compressed-tar' => 'tZ',
		'application/x-core' => '',
		'application/x-cpio' => 'cpio',
		'application/x-cpio-compressed' => '',
		'application/x-csh' => 'csh',
		'application/x-cue' => 'cue',
		'application/x-dar' => 'dar',
		'application/x-dbf' => 'dbf',
		'application/x-dc-rom' => '',
		'application/x-deb' => 'db',
		'application/x-designer' => '',
		'application/x-desktop' => '',
		'application/x-dia-diagram' => '',
		'application/x-dia-shape' => '',
		'application/x-dvi' => 'dvi',
		'application/x-e-theme' => '',
		'application/x-egon' => '',
		'application/x-executable' => 'exe',
		'application/x-fictionbook+xml' => '',
		'application/x-fluid' => '',
		'application/x-font-afm' => '',
		'application/x-font-bdf' => '',
		'application/x-font-dos' => '',
		'application/x-font-framemaker' => '',
		'application/x-font-libgrx' => '',
		'application/x-font-linux-psf' => '',
		'application/x-font-otf' => '',
		'application/x-font-pcf' => '',
		'application/x-font-speedo' => '',
		'application/x-font-sunos-news' => '',
		'application/x-font-tex' => '',
		'application/x-font-tex-tfm' => '',
		'application/x-font-ttf' => 'ttf',
		'application/x-font-ttx' => 'ttx',
		'application/x-font-type1' => '',
		'application/x-font-vfont' => '',
		'application/x-frame' => '',
		'application/x-gameboy-rom' => '',
		'application/x-gba-rom' => '',
		'application/x-gdbm' => '',
		'application/x-gedcom' => '',
		'application/x-genesis-rom' => '',
		'application/x-gettext-translation' => '',
		'application/x-glade' => '',
		'application/x-gmc-link' => '',
		'application/x-gnucash' => '',
		'application/x-gnumeric' => '',
		'application/x-gnuplot' => '',
		'application/x-go-sgf' => '',
		'application/x-graphite' => '',
		'application/x-gtktalog' => '',
		'application/x-gz-font-linux-psf' => '',
		'application/x-gzdvi' => 'gzdvi',
		'application/x-gzip' => 'gz',
		'application/x-gzpdf' => 'gzpdf',
		'application/x-gzpostscript' => 'gzps',
		'application/x-hdf' => 'hdf',
		'application/x-hwp' => 'hwp',
		'application/x-hwt' => 'hwt',
		'application/x-ica' => 'ica',
		'application/x-ipod-firmware' => '',
		'application/x-it87' => 'it87',
		'application/x-java' => 'java',
		'application/x-java-archive' => 'jar',
		'application/x-java-jce-keystore' => '',
		'application/x-java-jnlp-file' => '',
		'application/x-java-keystore' => '',
		'application/x-java-pack200' => '',
		'application/x-jbuilder-project' => '',
		'application/x-karbon' => '',
		'application/x-kchart' => '',
		'application/x-kexi-connectiondata' => '',
		'application/x-kexiproject-shortcut' => '',
		'application/x-kexiproject-sqlite2' => '',
		'application/x-kexiproject-sqlite3' => '',
		'application/x-kformula' => '',
		'application/x-killustrator' => '',
		'application/x-kivio' => '',
		'application/x-kontour' => '',
		'application/x-kpovmodeler' => '',
		'application/x-kpresenter' => '',
		'application/x-krita' => '',
		'application/x-kspread' => '',
		'application/x-kspread-crypt' => '',
		'application/x-ksysv-package' => '',
		'application/x-kugar' => '',
		'application/x-kword' => '',
		'application/x-kword-crypt' => '',
		'application/x-lha' => 'lha',
		'application/x-lhz' => 'lhz',
		'application/x-lrzip' => '',
		'application/x-lrzip-compressed-tar' => '',
		'application/x-lyx' => 'lyx',
		'application/x-lzip' => '',
		'application/x-lzma' => '',
		'application/x-lzma-compressed-tar' => '',
		'application/x-lzop' => '',
		'application/x-m4' => 'm4',
		'application/x-macbinary' => '',
		'application/x-magicpoint' => '',
		'application/x-markaby' => '',
		'application/x-matroska' => '',
		'application/x-mif' => '',
		'application/x-mozilla-bookmarks' => '',
		'application/x-ms-dos-executable' => 'exe',
		'application/x-ms-wim' => '',
		'application/x-msi' => 'msi',
		'application/x-mswinurl' => '',
		'application/x-mswrite' => '',
		'application/x-msx-rom' => '',
		'application/x-n64-rom' => '',
		'application/x-nautilus-link' => '',
		'application/x-navi-animation' => '',
		'application/x-nes-rom' => '',
		'application/x-netcdf' => '',
		'application/x-netshow-channel' => '',
		'application/x-nintendo-ds-rom' => '',
		'application/x-object' => '',
		'application/x-ole-storage' => '',
		'application/x-oleo' => '',
		'application/x-pak' => '',
		'application/x-palm-database' => '',
		'application/x-par2' => '',
		'application/x-pef-executable' => '',
		'application/x-perl' => 'pl',
		'application/x-php' => 'php',
		'application/x-pkcs12' => '',
		'application/x-pkcs7-certificates' => '',
		'application/x-planperfect' => '',
		'application/x-pocket-word' => '',
		'application/x-profile' => '',
		'application/x-pw' => '',
		'application/x-python-bytecode' => 'pyc',
		'application/x-quattropro' => '',
		'application/x-quicktime-media-link' => '',
		'application/x-qw' => '',
		'application/x-rar' => 'rar',
		'application/x-reject' => '',
		'application/x-rpm' => 'rpm',
		'application/x-ruby' => 'rb',
		'application/x-sami' => '',
		'application/x-sc' => '',
		'application/x-shar' => 'shar',
		'application/x-shared-library-la' => 'so',
		'application/x-sharedlib' => 'so',
		'application/x-shellscript' => 'sh',
		'application/x-shockwave-flash' => 'swf',
		'application/x-shorten' => '',
		'application/x-siag' => '',
		'application/x-slp' => '',
		'application/x-smaf' => '',
		'application/x-sms-rom' => '',
		'application/x-snes-rom' => '',
		'application/x-spss-por' => '',
		'application/x-spss-sav' => '',
		'application/x-sqlite2' => '',
		'application/x-sqlite3' => '',
		'application/x-stuffit' => 'stf',
		'application/x-subrip' => '',
		'application/x-sv4cpio' => '',
		'application/x-sv4crc' => '',
		'application/x-t602' => '',
		'application/x-tar' => 'tar',
		'application/x-tarz' => 'tgz',
		'application/x-tex-gf' => '',
		'application/x-tex-pk' => '',
		'application/x-tgif' => '',
		'application/x-theme' => 'theme',
		'application/x-toutdoux' => '',
		'application/x-trash' => '',
		'application/x-troff-man' => '',
		'application/x-troff-man-compressed' => '',
		'application/x-tzo' => '',
		'application/x-ufraw' => '',
		'application/x-ustar' => '',
		'application/x-wais-source' => '',
		'application/x-windows-themepack' => '',
		'application/x-wpg' => '',
		'application/x-x509-ca-cert' => '',
		'application/x-xbel' => '',
		'application/x-xliff' => '',
		'application/x-xpinstall' => '',
		'application/x-xz' => '',
		'application/x-xz-compressed-tar' => '',
		'application/x-yaml' => 'yaml',
		'application/x-zerosize' => '',
		'application/x-zoo' => '',
		'application/xhtml+xml' => 'html',
		'application/xml' => 'xml',
		'application/xml-dtd' => 'dtd',
		'application/xml-external-parsed-entity' => '',
		'application/xslt+xml' => 'xslt',
		'application/xspf+xml' => 'xsfp',
		'application/zip' => 'zip',
		'audio/AMR' => '',
		'audio/AMR-WB' => '',
		'audio/ac3' => '',
		'audio/annodex' => '',
		'audio/basic' => '',
		'audio/flac' => 'flac',
		'audio/midi' => 'midi',
		'audio/mp2' => 'mp2',
		'audio/mp4' => 'mp4',
		'audio/mpeg' => 'mpeg',
		'audio/ogg' => 'ogg',
		'audio/prs.sid' => '',
		'audio/vnd.rn-realaudio' => '',
		'audio/webm' => '',
		'audio/x-adpcm' => '',
		'audio/x-aifc' => 'aifc',
		'audio/x-aiff' => 'aiff',
		'audio/x-aiffc' => 'aiffc',
		'audio/x-ape' => 'ape',
		'audio/x-flac+ogg' => 'flac',
		'audio/x-gsm' => 'gsm',
		'audio/x-iriver-pla' => '',
		'audio/x-it' => '',
		'audio/x-m4b' => 'm4b',
		'audio/x-matroska' => '',
		'audio/x-minipsf' => '',
		'audio/x-mo3' => 'mo3',
		'audio/x-mod' => 'mod',
		'audio/x-mpegurl' => 'mpegurl',
		'audio/x-ms-asx' => 'asx',
		'audio/x-ms-wma' => 'wma',
		'audio/x-musepack' => '',
		'audio/x-psf' => 'psf',
		'audio/x-psflib' => '',
		'audio/x-riff' => 'riff',
		'audio/x-s3m' => 's3m',
		'audio/x-scpls' => '',
		'audio/x-speex' => 'spx',
		'audio/x-speex+ogg' => 'spx',
		'audio/x-stm' => 'stm',
		'audio/x-tta' => 'tta',
		'audio/x-voc' => 'voc',
		'audio/x-vorbis+ogg' => 'ogg',
		'audio/x-wav' => 'wav',
		'audio/x-wavpack' => 'wav',
		'audio/x-wavpack-correction' => 'wav',
		'audio/x-xi' => 'xi',
		'audio/x-xm' => 'xm',
		'audio/x-xmf' => 'xmf',
		'image/bmp' => 'bmp',
		'image/cgm' => 'cgm',
		'image/dpx' => 'dpx',
		'image/fax-g3' => 'fax',
		'image/fits' => 'fits',
		'image/g3fax' => 'g3fax',
		'image/gif' => 'gif',
		'image/ief' => 'lef',
		'image/jp2' => 'jp2',
		'image/jpeg' => 'jpg',
		'image/openraster' => '',
		'image/png' => 'png',
		'image/rle' => 'rle',
		'image/svg+xml' => 'svg',
		'image/svg+xml-compressed' => 'svgc',
		'image/tiff' => 'tif',
		'image/vnd.adobe.photoshop' => 'psd',
		'image/vnd.djvu' => '',
		'image/vnd.dwg' => 'dwg',
		'image/vnd.dxf' => 'dxf',
		'image/vnd.microsoft.icon' => 'ico',
		'image/vnd.ms-modi' => '',
		'image/vnd.rn-realpix' => '',
		'image/vnd.wap.wbmp' => '',
		'image/x-3ds' => '3ds',
		'image/x-adobe-dng' => 'dng',
		'image/x-applix-graphics' => '',
		'image/x-bzeps' => '',
		'image/x-canon-cr2' => 'cr2',
		'image/x-canon-crw' => 'crw',
		'image/x-cmu-raster' => '',
		'image/x-compressed-xcf' => 'xcf',
		'image/x-dcraw' => '',
		'image/x-dds' => 'dds',
		'image/x-dib' => 'dib',
		'image/x-emf' => 'emf',
		'image/x-eps' => 'eps',
		'image/x-exr' => 'exr',
		'image/x-fpx' => 'fpx',
		'image/x-fuji-raf' => 'raf',
		'image/x-gzeps' => 'gzeps',
		'image/x-icns' => '',
		'image/x-iff' => 'iff',
		'image/x-ilbm' => 'ilbm',
		'image/x-jng' => 'jng',
		'image/x-kodak-dcr' => 'dcr',
		'image/x-kodak-k25' => 'k25',
		'image/x-kodak-kdc' => 'kdc',
		'image/x-lwo' => 'lwo',
		'image/x-lws' => 'lws',
		'image/x-macpaint' => '',
		'image/x-minolta-mrw' => 'mrw',
		'image/x-msod' => 'msod',
		'image/x-niff' => 'niff',
		'image/x-nikon-nef' => 'nef',
		'image/x-olympus-orf' => 'orf',
		'image/x-panasonic-raw' => 'praw',
		'image/x-pcx' => 'pcx',
		'image/x-pentax-pef' => 'pef',
		'image/x-photo-cd' => 'pcd',
		'image/x-pict' => 'pict',
		'image/x-portable-anymap' => 'amp',
		'image/x-portable-bitmap' => 'pbm',
		'image/x-portable-graymap' => 'pgm',
		'image/x-portable-pixmap' => 'ppx',
		'image/x-quicktime' => 'mov',
		'image/x-rgb' => 'rgb',
		'image/x-sgi' => 'sgi',
		'image/x-sigma-x3f' => 'x3f',
		'image/x-skencil' => 'sk1',
		'image/x-sony-arw' => 'arw',
		'image/x-sony-sr2' => 'sr2',
		'image/x-sony-srf' => 'srf',
		'image/x-sun-raster' => 'ras',
		'image/x-tga' => 'tga',
		'image/x-win-bitmap' => 'bmp',
		'image/x-wmf' => 'wmf',
		'image/x-xbitmap' => 'xbm',
		'image/x-xcf' => 'xcf',
		'image/x-xcursor' => '',
		'image/x-xfig' => 'xfig',
		'image/x-xpixmap' => 'xpm',
		'image/x-xwindowdump' => '',
		'inode/blockdevice' => '',
		'inode/chardevice' => '',
		'inode/directory' => '',
		'inode/fifo' => '',
		'inode/mount-point' => '',
		'inode/socket' => '',
		'inode/symlink' => '',
		'message/delivery-status' => '',
		'message/disposition-notification' => '',
		'message/external-body' => '',
		'message/news' => '',
		'message/partial' => '',
		'message/rfc822' => '',
		'message/x-gnu-rmail' => '',
		'model/vrml' => '',
		'multipart/alternative' => '',
		'multipart/appledouble' => '',
		'multipart/digest' => '',
		'multipart/encrypted' => '',
		'multipart/mixed' => '',
		'multipart/related' => '',
		'multipart/report' => '',
		'multipart/signed' => '',
		'multipart/x-mixed-replace' => '',
		'text/cache-manifest' => '',
		'text/calendar' => 'ics',
		'text/css' => 'css',
		'text/csv' => 'csv',
		'text/directory' => '',
		'text/enriched' => '',
		'text/html' => 'html',
		'text/htmlh' => 'htmlh',
		'text/plain' => 'txt',
		'text/rfc822-headers' => '',
		'text/richtext' => 'rtf',
		'text/sgml' => 'sgml',
		'text/spreadsheet' => 'xls',
		'text/tab-separated-values' => 'tsv',
		'text/troff' => 'tr',
		'text/vnd.graphviz' => '',
		'text/vnd.rn-realtext' => '',
		'text/vnd.sun.j2me.app-descriptor' => '',
		'text/vnd.trolltech.linguist' => '',
		'text/vnd.wap.wml' => '',
		'text/vnd.wap.wmlscript' => '',
		'text/x-adasrc' => '',
		'text/x-authors' => '',
		'text/x-bibtex' => 'bib',
		'text/x-c++hdr' => 'cph',
		'text/x-c++src' => 'cpp',
		'text/x-changelog' => '',
		'text/x-chdr' => '',
		'text/x-cmake' => '',
		'text/x-copying' => '',
		'text/x-credits' => '',
		'text/x-csharp' => 'C#',
		'text/x-csrc' => 'c',
		'text/x-dcl' => '',
		'text/x-dsl' => '',
		'text/x-dsrc' => '',
		'text/x-eiffel' => '',
		'text/x-emacs-lisp' => 'el',
		'text/x-erlang' => 'er',
		'text/x-fortran' => 'for',
		'text/x-gettext-translation' => '',
		'text/x-gettext-translation-template' => '',
		'text/x-google-video-pointer' => '',
		'text/x-haskell' => 'has',
		'text/x-iMelody' => '',
		'text/x-idl' => '',
		'text/x-install' => '',
		'text/x-iptables' => '',
		'text/x-java' => 'java',
		'text/x-ldif' => '',
		'text/x-lilypond' => 'ly',
		'text/x-literate-haskell' => 'lhs',
		'text/x-log' => 'log',
		'text/x-lua' => 'lua',
		'text/x-makefile' => '',
		'text/x-matlab' => '',
		'text/x-microdvd' => '',
		'text/x-moc' => 'moc',
		'text/x-mof' => 'mof',
		'text/x-mpsub' => 'mpsub',
		'text/x-mrml' => 'mrml',
		'text/x-ms-regedit' => 'reg',
		'text/x-mup' => 'mup',
		'text/x-nfo' => 'nfo',
		'text/x-objcsrc' => '',
		'text/x-ocaml' => 'oca',
		'text/x-ocl' => 'ocl',
		'text/x-opml+xml' => 'opml',
		'text/x-pascal' => 'pas',
		'text/x-patch' => 'patch',
		'text/x-python' => 'py',
		'text/x-readme' => '',
		'text/x-rpm-spec' => 'rpms',
		'text/x-scheme' => 'sch',
		'text/x-setext' => '',
		'text/x-sql' => 'sql',
		'text/x-ssa' => 'ssa',
		'text/x-subviewer' => '',
		'text/x-svhdr' => '',
		'text/x-svsrc' => '',
		'text/x-tcl' => 'tcl',
		'text/x-tex' => 'tex',
		'text/x-texinfo' => 'texi',
		'text/x-troff-me' => 'tr',
		'text/x-troff-mm' => 'tr',
		'text/x-troff-ms' => 'tr',
		'text/x-txt2tags' => '',
		'text/x-uil' => 'uil',
		'text/x-uri' => '',
		'text/x-vala' => 'vala',
		'text/x-verilog' => '',
		'text/x-vhdl' => 'vhdl',
		'text/x-xmi' => 'xmi',
		'text/x-xslfo' => '',
		'text/xmcd' => 'xmcd',
		'video/3gpp' => '3gpp',
		'video/3gpp2' => '3gpp',
		'video/annodex' => '',
		'video/dv' => 'dv',
		'video/isivideo' => 'isi',
		'video/mp2t' => 'mp2',
		'video/mp4' => 'mp4',
		'video/mpeg' => 'mpg',
		'video/ogg' => 'ogg',
		'video/quicktime' => 'mov',
		'video/vivo' => 'vivo',
		'video/vnd.rn-realvideo' => '',
		'video/wavelet' => 'rdb',
		'video/webm' => 'webm',
		'video/x-anim' => 'amv',
		'video/x-flic' => 'flic',
		'video/x-flv' => 'flv',
		'video/x-javafx' => '',
		'video/x-matroska' => 'mkv',
		'video/x-mng' => 'mng',
		'video/x-ms-asf' => 'asf',
		'video/x-ms-wmv' => 'wmv',
		'video/x-msvideo' => 'wmv',
		'video/x-nsv' => 'nsv',
		'video/x-ogm+ogg' => 'ogm',
		'video/x-sgi-movie' => 'sgim',
		'video/x-theora+ogg' => '',
		'x-content/audio-cdda' => 'cdda',
		'x-content/audio-dvd' => 'dvd',
		'x-content/audio-player' => '',
		'x-content/blank-bd' => 'bd',
		'x-content/blank-cd' => 'cd',
		'x-content/blank-dvd' => 'dvd',
		'x-content/blank-hddvd' => 'hddvd',
		'x-content/ebook-reader' => '',
		'x-content/image-dcf' => 'dcf',
		'x-content/image-picturecd' => '',
		'x-content/software' => '',
		'x-content/unix-software' => '',
		'x-content/video-bluray' => 'bd',
		'x-content/video-dvd' => 'dvd',
		'x-content/video-hddvd' => 'hddvd',
		'x-content/video-svcd' => 'svcd',
		'x-content/video-vcd' => 'vcd',
		'x-content/win32-software' => 'exe',
		'x-epoc/x-sisx-app' => '',
		
		
	);
} // END class MimeTypeInfo
