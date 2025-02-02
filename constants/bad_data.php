<?php
// Some data we get from outside sources is bad or at least mis-defined
// Use lower case for all of these, and then compare to a lower cased version
const HAS_NO_VOLUME = array("zookeys", "studia hibernica", "analecta hibernica", "british art studies", "der spiegel");  // Some journals have issues only, no volume numbers
const BAD_ACCEPTED_MANUSCRIPT_TITLES = array("oup accepted manuscript", "placeholder for bad pdf file", 
                                             "placeholder", "symbolic placeholder", "[placeholder]", 
                                             "placeholder for arabic language transliteration");
const BAD_AUTHORS = array("unknown", "missing", "- -.", "- -", "no authorship indicated", "no authorship", "no author",
                           "no authors");
const NON_HUMAN_AUTHORS = array('collaborat', 'reporter', 'journalist', 'correspondent', 'anchor', 'staff', 'foreign',
                                'national', 'endowment', ' for the ', 'humanities', 'committee',
                                'society', ' of america', 'association', ' at the ', 'board of ',
                                'corporation', 'incorporated', 'editorial', 'university', 'dept. of', 'department');
const BAD_PUBLISHERS = array('london', 'edinburgi', 'edinburgh', 'no publisher', 'no publisher given',
                             'no publisher specified', 'unknown', 'publisher not identified');

const PUBLISHERS_ARE_WORKS = array('the san diego union-tribune', 'forbes', 'salon', 'san jose mercury news', 'new york times',
                                   'the new york times'); // LOWER CASE!

// Catch 'authors' such as "hearst magazines", "time inc", "nielsen business media, inc"
// Ordered alphabetically.
const PUBLISHER_ENDINGS = ["books", "corporation", 'centre', 'center', 'company', "inc.", "inc", "magazines",
                           'museum', "press", "publishers", "publishing", 'science'];
const BAD_TITLES = array("unknown", "missing", "arxiv e-prints", "arxiv mathematics e-prints", 
                         "ssrn electronic journal", "dissertations available from proquest",
                         "ebscohost login",  "library login", "google groups", "sciencedirect", "cur_title",
                         "wordpress › error");
const IN_PRESS_ALIASES = array("in press", "inpress", "pending", "published", 
                               "published online", "no-no", "n/a", "online ahead of print", 
                               "unpublished", "unknown", "tba", "forthcoming", "in the press", 
                               "na", "submitted", "tbd", "missing");
const NON_JOURNAL_BIBCODES = array('arXiv', 'gr.qc', 'hep.ex', 'hep.lat', 'hep.ph', 'hep.th', 'astro.ph',
                                   'math', 'nucl.ex', 'nucl.th', 'physics', 'quant.ph', 'alg.geom',
                                   'cond.mat', 'cs.', 'econ.', 'eess.', 'nlin.');
const NON_PUBLISHERS = ['books.google', 'google books', 'google news', 'google.co', 'amazon.com',
                        'zenodo', 'archive.org', 'citeseerx.ist.psu.edu']; // Google Inc is a valid publisher, however.
const BAD_ZOTERO_TITLES = ['Browse publications', 'Central Authentication Service', 'http://', 'https://',
                                 'ZbMATH - the first resource for mathematics', 'MR: Matches for:',
                                 ' Log In', 'Log In ', 'Sign in', 'Bookmarkable URL intermediate page', 'Shibboleth Authentication Request',
                                 'domain for sale', 'website for sale', 'domain is for sale', 'website is for sale',
                                 'lease this domain', 'domain available', 'metaTags', 'An Error Occurred', 'User Cookie',
                                 'Cookies Disabled', 'page not found', '411 error', 'url not found',
                                 'limit exceeded', 'Error Page', '}}', '{{', 'EU Login', 'bad gateway', 'Captcha',
                                 '.com', '.gov', '.org', 'View PDF', 'Wayback Machine', 'does not exist', 
                                 'Subscribe to read', 'Wiley Online Library', 'pagina is niet gevonden',
                                 'Zoeken in over NA', 'na een 404', '404 error', 'Account Suspended',
                                 'Error 404', 'EZProxy', 'EBSCOhost Login'];

const CANONICAL_PUBLISHER_URLS = array ('elsevier.com', 'springer.com', 'sciencedirect.com', 'tandfonline.com',
                                'taylorandfrancis.com', 'wiley.com', 'sagepub.com', 'sagepublications.com',
                                'scielo.org', 'scielo.br', 'degruyter.com', 'hindawi.com', 'inderscience.com',
                                'cambridge.org', '.oup.com', 'nature.com', 'macmillan.com', 'ieeexplore.ieee.org',
                                'worldscientific.com', 'iospress.com', 'iospress.nl', 'pnas.org', 'journals.ametsoc.org',
                                'pubs.geoscienceworld.org', 'pubs.rsc.org', 'journals.uchicago.edu',
                                'annualreviews.org', 'aip.scitation.org', 'psyche.entclub.org', 'thelancet.com',
                                'amjbot.org', 'gsapubs.org', 'jwildlifedis.org', 'msptm.org', 'nrcresearchpress.',
                                'fundacionmenteclara.org.ar',
                                //  Below are journal search engines
                                '.serialssolutions.com', '.ebscohost.com',
                                //  Below are proxys
                                'proxy.libraries', 'proxy.lib.', '.ezproxy.', '-ezproxy.', '/ezproxy.',
                                //  Below are sites that are simply DOI resolvers, like dx.doi.org
                                'doi.library.ubc.ca');

const PROXY_HOSTS_TO_DROP = array('proxy.libraries', 'proxy.lib.', '.ezproxy.', '-ezproxy.', '/ezproxy.',
                                  '.serialssolutions.com', '.ebscohost.com'); // Drop these if there is a valid DOI

const WEB_NEWSPAPERS = array('bbc news', 'bbc', 'news.bbc.co.uk', 'bbc sports', 'bbc sport');

const NO_DATE_WEBSITES = array('wikipedia.org', 'web.archive.org', 'perma-archives.org', 'webarchive.proni.gov.uk', 'perma.cc',
                              'wayback', 'web.archive.bibalex.org', 'web.petabox.bibalex.org', 'webharvest.gov', 'archive.wikiwix.com',
                              'archive.is', 'archive-it.org', 'nationalarchives.gov.uk', 'freezepage.com', 'webcitation.org',
                              'waybackmachine.org', 'siarchives.si.edu', 'gutenberg.org');

const ZOTERO_AVOID_REGEX = array("twitter\.",               // This should be {{cite tweet}}
                                 "youtube\.", "youtu\.be",  // This should be {{cite AV media}}
                                 "books\.google\.",         // We have special google books code
                                 "google\.com/search",      // Google search results
                                 "jstor\.org/stable/",      // We have special jstor code
                                 "ned\.ipac\.caltech\.edu");// Gives no real title
const NON_JOURNAL_WEBSITES = array('cnn.com/', 'foxnews.com/', 'msnbc.com/', 'nbcnews.com/', 'abcnews.com/', 'cbs.com/', 
                                   'cbsnews.com/', 'abc.com/', 'bbc.com/', 'bbc.co.uk/', 'apnews.com/',
                                   '.ap.org/', 'nytimes.com/', 'theguardian.com/', 'washingtonpost.com/',
                                   'newyorker.com/', 'independent.co.uk/', 'cnbc.com/', 'vanityfair.com/',
                                   'theatlantic.com/', '-news.co.uk/', 'news.google.com/', 'jpl.nasa.gov/',
                                   'gsfc.nasa.gov/', 'solarsystem.nasa.gov/', 'latimes.com/',
                                   'usatoday.com/', 'wsj.com/', 'haaretz.com/', 'buzzfeed.com/',
                                   'aljazeera.com/', 'vox.com/', 'reuters.com/', 'dailynews.com/', 
                                   'newsweek.com/', 'monitor.com/', 'observer.com/', '.pbs.org/', '.bbm.ca/', '/bbm.ca/',
                                   'mediaincanada.com/', 'cbspressexpress.com/', 'deadline.com/', 'zap2it.com/',
                                   'yourentertainmentnow.com/', 'shows.ctv.ca/' ,'toronto.com/'); 
                                   // Just a list of ones that are obvious.  Add ones that time-out as we find them
                                   // bbm.ca is short enough that we add /bbm.ca/ and .bbm.ca/ since we don't want to grab too many sites

const GOOD_10_1093_DOIS = array( // March 2019 list
          'abbs', 'abm', 'abt', 'acn', 'adaptation', 'advances', 'ae', 'aepp', 'aesa', 'af', 'afraf',
          'ageing', 'ahr', 'ajae', 'ajcl', 'ajcn', 'ajcp', 'aje', 'ajh', 'ajhp', 'ajj', 'ajlh',
          'alcalc', 'aler', 'alh', 'alrr', 'amt', 'analysis', 'annhyg', 'annonc', 'antitrust', 'aob',
          'aobpla', 'applij', 'arbitration', 'aristotelian', 'aristoteliansupp', 'asj', 'asjopenforum', 'astrogeo', 'auk', 'beheco',
          'bfg', 'bib', 'bioinformatics', 'biolinnean', 'biolreprod', 'biomedgerontology', 'biomet', 'biomethods', 'bioscience', 'biostatistics',
          'bjaesthetics', 'bjc', 'bjps', 'bjsw', 'bmb', 'botlinnean', 'brain', 'btcint', 'bybil', 'camqtly',
          'carcin', 'cardiovascres', 'cb', 'ccc', 'cdj', 'cdn', 'ce', 'cercor', 'cesifo', 'chemse',
          'chinesejil', 'chromsci', 'cid', 'cjcl', 'cje', 'cjip', 'cjres', 'ckj', 'clp', 'cmlj',
          'comjnl', 'comnet', 'condor', 'conphys', 'cpe', 'criticalvalues', 'crj', 'crohnscolitis360', 'cs', 'ct',
          'cww', 'cybersecurity', 'cz', 'database', 'dh', 'dnaresearch', 'dote', 'dsh', 'ecco-jcc', 'ecco-jccs',
          'economicpolicy', 'ectj', 'edrv', 'ee', 'eep', 'ehjcimaging', 'ehjcr', 'ehjcvp', 'ehjqcco', 'ehr',
          'eic', 'ej', 'ejcts', 'ejil', 'ejo', 'eltj', 'em', 'emph', 'endo', 'english',
          'envhis', 'envihistrevi', 'epirev', 'erae', 'ereh', 'eshremonographs', 'esr', 'eurheartj', 'eurheartjsupp', 'europace',
          'eurpub', 'fampra', 'femsec', 'femsle', 'femspd', 'femsre', 'femsyr', 'fh', 'fmls', 'foreconshist',
          'forestry', 'forestscience', 'fpa', 'fqs', 'fs', 'fsb', 'gastro', 'gbe', 'geronj', 'gerontologist',
          'gh', 'gigascience', 'gji', 'globalsummitry', 'glycob', 'gsmnras', 'hcr', 'heapol', 'heapro', 'her',
          'hgs', 'hmg', 'hrlr', 'hropen', 'hsw', 'humrep', 'humupd', 'hwj', 'ia', 'ib',
          'ibdjournal', 'icb', 'icc', 'icesjms', 'icon', 'icsidreview', 'icvts', 'idpl', 'ije', 'ijl',
          'ijlct', 'ijlit', 'ijnp', 'ijpor', 'ijrl', 'ijtj', 'ilarjournal', 'ilj', 'imaiai', 'imajna',
          'imaman', 'imamat', 'imamci', 'imammb', 'imatrm', 'imrn', 'imrp', 'imrs', 'innovateage', 'insilicoplants',
          'integrablesystems', 'inthealth', 'intimm', 'intqhc', 'iob', 'ips', 'irap', 'isd', 'isle', 'isp',
          'isq', 'isr', 'itnow', 'iwc', 'jaar', 'jac', 'jae', 'jah', 'jamia', 'jamiaopen',
          'japr', 'jas', 'jat', 'jb', 'jbcr', 'jbi', 'jcag', 'jcb', 'jcem', 'jcle',
          'jcmc', 'jcr', 'jcs', 'jcsl', 'jdh', 'jdsde', 'jeclap', 'jee', 'jeea', 'jel',
          'jes', 'jfec', 'jfr', 'jge', 'jhc', 'jhered', 'jhmas', 'jhps', 'jhrp', 'jhs',
          'jicj', 'jid', 'jids', 'jiel', 'jigpal', 'jinsectscience', 'jiplp', 'jipm', 'jis', 'jjco',
          'jla', 'jlb', 'jleo', 'jmammal', 'jmcb', 'jme', 'jmicro', 'jmp', 'jmt', 'jn',
          'jnci', 'jncics', 'jncimono', 'jnen', 'joc', 'joeg', 'jof', 'jogss', 'jole', 'jos',
          'jpart', 'jpe', 'jpepsy', 'jpids', 'jpo', 'jpubhealth', 'jrls', 'jrr', 'jrs', 'jscr',
          'jsh', 'jss', 'jssam', 'jtm', 'jts', 'jue', 'jvc', 'jwelb', 'jxb', 'labmed',
          'lawfam', 'leobaeck', 'library', 'litimag', 'litthe', 'logcom', 'lpr', 'lril', 'maghis', 'mbe',
          'medlaw', 'melus', 'mend', 'migration', 'milmed', 'mind', 'mj', 'ml', 'mmy', 'mnras',
          'mnrasl', 'molehr', 'mollus', 'monist', 'mq', 'mspecies', 'mtp', 'mts', 'musictherapy', 'mutage',
          'nar', 'nass', 'nc', 'ndt', 'neuro-oncology', 'neurosurgery', 'njaf', 'nop', 'nq', 'nsr',
          'ntr', 'nutritionreviews', 'oaj', 'occmed', 'oep', 'ofid', 'ohr', 'ojlr', 'ojls', 'omcr',
          'ons', 'oq', 'oxrep', 'pa', 'painmedicine', 'pasj', 'past', 'pch', 'pcm', 'pcp',
          'peds', 'petrology', 'phe', 'philmat', 'plankt', 'policing', 'poq', 'ppar', 'ppmg', 'ppr',
          'pq', 'proceedingslinnean', 'ps', 'psychsocgerontology', 'ptep', 'ptj', 'ptp', 'ptps', 'publius', 'qje',
          'qjmam', 'qjmath', 'qjmed', 'raps', 'rb', 'rcfs', 'reep', 'res', 'restud', 'rev',
          'rfs', 'rheumap', 'rheumatology', 'rof', 'rpc', 'rpd', 'rsq', 'scan', 'schizophreniabulletin', 'screen',
          'ser', 'sf', 'shm', 'sjaf', 'sleep', 'slr', 'socpro', 'socrel', 'sp', 'spp',
          'sq', 'ssjj', 'sw', 'swr', 'swra', 'synbio', 'sysbio', 'tandt', 'tas', 'tbm',
          'tcbh', 'teamat', 'toxsci', 'transactionslinnean', 'transactionslinneanbot', 'transactionslinneanzoo', 'treephys', 'tropej', 'trstmh', 'tse',
          'ulr', 've', 'wber', 'wbro', 'whq', 'wjaf', 'workar', 'yel', 'yielaw', 'ywcct',
          'ywes', 'zoolinnean');


// List of things to not print links to, since they occur all the time
const AVOIDED_LINKS = array('', 'Digital_object_identifier', 'JSTOR', 'Website', 'International_Standard_Book_Number',
                            'Library_of_Congress_Control_Number', 'Handle_System', 'PubMed_Central', 'PubMed',
                            'PubMed_Identifier', 'Bibcode', 'International_Standard_Serial_Number', 'bioRxiv',
                            'CiteSeerX', 'Zentralblatt_MATH', 'Jahrbuch_über_die_Fortschritte_der_Mathematik',
                            'Mathematical_Reviews', 'Office_of_Scientific_and_Technical_Information',
                            'Request_for_Comments', 'Social_Science_Research_Network', 'Zentralblatt_MATH',
                            'Open_Library', 'ArXiv', 'OCLC', 'Cf.');
