<?php
// path to request this page by javascript
define ( 'path_http', '' );

// path to the config file
define ( 'config_file_path', 'config.php' );

// interval to run updata_status() in miliseconds
// It should be a multiple of 4 because it is multiplied by 1.25 in some place.
define ( 'update_status_interval', 2000 );

// number of seconds of bandwidth data to be stored
define ( 'bandwidth_data_size', 601 );

// number of messages stored by the browser
define ( 'messages_data_size', 65536 );

define ( 'tc_connection_method_network', 1 );
define ( 'tc_connection_method_unix_socket', 2 );
define ( 'tc_connection_secure_none', 0 );
define ( 'tc_connection_secure_ssl', 1 );
define ( 'tc_connection_secure_tls', 2 );
define ( 'tc_connection_auth_none', 0 );
define ( 'tc_connection_auth_password', 1 );
define ( 'tc_connection_auth_cookie', 2 );
define ( 'tc_max_result_length', 8192 );
define ( 'tor_options_number', 296 );
define ( 'update_status_getinfo_1_count', 7 );

include config_file_path;
$update_config = 0;
$error_message = '';
$tor_options_name = array ();
$tor_options_value = array ();
$tor_options_type = array ();
$tor_options_name_length = array ();
$tor_options_name_reverse = array ();
$tor_options_number = 0;
$tor_options_default_value = array ();
$tor_version_string = '';

/*
 * The descriptions are from tor (1) man page.
 * The authors are Roger Dingledine [arma at mit.edu], Nick Mathewson [nickm at alum.mit.edu].
 */
$tor_options_description = array (
		'BandwidthRate' => '<p class="tor_option_description"><b>BandwidthRate</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">A token bucket limits the
				average incoming bandwidth usage on this node to the
				specified number of bytes per second, and the average
				outgoing bandwidth usage to that same value. If you want to
				run a relay in the public network, this needs to be <i>at
				the very least</i> 30 KBytes (that is, 30720 bytes).
				(Default: 1 GByte)</p><p class="tor_option_description_indented">With this
				option, and in other options that take arguments in bytes,
				KBytes, and so on, other formats are also supported.
				Notably, "KBytes" can also be written as
				"kilobytes" or "kb"; "MBytes"
				can be written as "megabytes" or "MB";
				"kbits" can be written as "kilobits";
				and so forth. Tor also accepts "byte" and
				"bit" in the singular. The prefixes
				"tera" and "T" are also recognized. If
				no units are given, we default to bytes. To avoid confusion,
				we recommend writing "bytes" or "bits"
				explicitly, since it’s easy to forget that
				"B" means bytes, not bits.</p>',
		'BandwidthBurst' => '<p class="tor_option_description"><b>BandwidthBurst</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">Limit the maximum token bucket
				size (also known as the burst) to the given number of bytes
				in each direction. (Default: 1 GByte)</p>',
		'MaxAdvertisedBandwidth' => '<p class="tor_option_description"><b>MaxAdvertisedBandwidth</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">If set, we will not advertise
				more than this amount of bandwidth for our BandwidthRate.
				Server operators who want to reduce the number of clients
				who ask to build circuits through them (since this is
				proportional to advertised bandwidth rate) can thus reduce
				the CPU demands on their server without impacting network
				performance.</p>',
		'RelayBandwidthRate' => '<p class="tor_option_description"><b>RelayBandwidthRate</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">If not 0, a separate token
				bucket limits the average incoming bandwidth usage for
				_relayed traffic_ on this node to the specified number of
				bytes per second, and the average outgoing bandwidth usage
				to that same value. Relayed traffic currently is calculated
				to include answers to directory requests, but that may
				change in future versions. (Default: 0)</p>',
		'RelayBandwidthBurst' => '<p class="tor_option_description"><b>RelayBandwidthBurst</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">If not 0, limit the maximum
				token bucket size (also known as the burst) for _relayed
				traffic_ to the given number of bytes in each direction.
				(Default: 0)</p>',
		'PerConnBWRate' => '<p class="tor_option_description"><b>PerConnBWRate</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">If set, do separate rate
				limiting for each connection from a non−relay. You
				should never need to change this value, since a
				network−wide value is published in the consensus and
				your relay will use that value. (Default: 0)</p>',
		'PerConnBWBurst' => '<p class="tor_option_description"><b>PerConnBWBurst</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">If set, do separate rate
				limiting for each connection from a non−relay. You
				should never need to change this value, since a
				network−wide value is published in the consensus and
				your relay will use that value. (Default: 0)</p>',
		'ClientTransportPlugin' => '<p class="tor_option_description"><b>ClientTransportPlugin</b>
				<i>transport</i> socks4|socks5 <i>IP</i>:<i>PORT</i>,
				<b>ClientTransportPlugin</b> <i>transport</i> exec
				<i>path−to−binary</i> [options]</p><p class="tor_option_description_indented">In its first form, when set
				along with a corresponding Bridge line, the Tor client
				forwards its traffic to a SOCKS−speaking proxy on
				"IP:PORT". It’s the duty of that proxy to
				properly forward the traffic to the bridge.</p><p class="tor_option_description_indented">In its second
				form, when set along with a corresponding Bridge line, the
				Tor client launches the pluggable transport proxy executable
				in <i>path−to−binary</i> using <i>options</i> as
				its command−line options, and forwards its traffic to
				it. It’s the duty of that proxy to properly forward
				the traffic to the bridge.</p>',
		'ServerTransportPlugin' => '<p class="tor_option_description"><b>ServerTransportPlugin</b>
				<i>transport</i> exec <i>path−to−binary</i>
				[options]</p><p class="tor_option_description_indented">The Tor relay launches the
				pluggable transport proxy in
				<i>path−to−binary</i> using <i>options</i> as
				its command−line options, and expects to receive
				proxied client traffic from it.</p>',
		'ServerTransportListenAddr' => '<p class="tor_option_description"><b>ServerTransportListenAddr</b>
				<i>transport IP</i>:<i>PORT</i></p><p class="tor_option_description_indented">When this option is set, Tor
				will suggest <i>IP</i>:<i>PORT</i> as the listening address
				of any pluggable transport proxy that tries to launch
				<i>transport</i>.</p>',
		'ServerTransportOptions' => '<p class="tor_option_description"><b>ServerTransportOptions</b>
				<i>transport k=v k=v</i> ...</p><p class="tor_option_description_indented">When this option is set, Tor
				will pass the <i>k=v</i> parameters to any pluggable
				transport proxy that tries to launch <i>transport</i>.</p><p class="tor_option_description_indented">(Example:
				ServerTransportOptions obfs45
				shared−secret=bridgepasswd
				cache=/var/lib/tor/cache)</p>',
		'ExtORPort' => '<p class="tor_option_description"><b>ExtORPort</b>
				[<i>address</i>:]<i>port</i>|<b>auto</b> Open this port to
				listen for Extended ORPort connections from your pluggable
				transports.</p>',
		'ExtORPortCookieAuthFile' => '<p class="tor_option_description"><b>ExtORPortCookieAuthFile</b>
				<i>Path</i></p><p class="tor_option_description_indented">If set, this option overrides
				the default location and file name for the Extended
				ORPort’s cookie file — the cookie file is needed
				for pluggable transports to communicate through the Extended
				ORPort.</p>',
		'ExtORPortCookieAuthFileGroupReadable' => '<p class="tor_option_description"><b>ExtORPortCookieAuthFileGroupReadable
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 0,
				don’t allow the filesystem group to read the Extended
				OR Port cookie file. If the option is set to 1, make the
				cookie file readable by the default GID. [Making the file
				readable by other groups is not yet implemented; let us know
				if you need this for some reason.] (Default: 0)</p>',
		'ConnLimit' => '<p class="tor_option_description"><b>ConnLimit</b>
				<i>NUM</i></p><p class="tor_option_description_indented">The minimum number of file
				descriptors that must be available to the Tor process before
				it will start. Tor will ask the OS for as many file
				descriptors as the OS will allow (you can find this by
				"ulimit −H −n"). If this number is
				less than ConnLimit, then Tor will refuse to start.</p><p class="tor_option_description_indented">You probably
				don’t need to adjust this. It has no effect on Windows
				since that platform lacks getrlimit(). (Default: 1000)</p>',
		'DisableNetwork' => '<p class="tor_option_description"><b>DisableNetwork
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set, we
				don’t listen for or accept any connections other than
				controller connections, and we close (and don’t
				reattempt) any outbound connections. Controllers sometimes
				use this option to avoid using the network until Tor is
				fully configured. (Default: 0)</p>',
		'ConstrainedSockets' => '<p class="tor_option_description"><b>ConstrainedSockets
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set, Tor will tell the
				kernel to attempt to shrink the buffers for all sockets to
				the size specified in <b>ConstrainedSockSize</b>. This is
				useful for virtual servers and other environments where
				system level TCP buffers may be limited. If you’re on
				a virtual server, and you encounter the "Error creating
				network socket: No buffer space available" message, you
				are likely experiencing this problem.</p><p class="tor_option_description_indented">The preferred
				solution is to have the admin increase the buffer pool for
				the host itself via /proc/sys/net/ipv4/tcp_mem or equivalent
				facility; this configuration option is a
				second−resort.</p><p class="tor_option_description_indented">The DirPort
				option should also not be used if TCP buffers are scarce.
				The cached directory requests consume additional sockets
				which exacerbates the problem.</p><p class="tor_option_description_indented">You should
				<b>not</b> enable this feature unless you encounter the
				"no buffer space available" issue. Reducing the
				TCP buffers affects window size for the TCP stream and will
				reduce throughput in proportion to round trip time on long
				paths. (Default: 0)</p>',
		'ConstrainedSockSize' => '<p class="tor_option_description"><b>ConstrainedSockSize</b>
				<i>N</i> <b>bytes</b>|<b>KBytes</b></p><p class="tor_option_description_indented">When <b>ConstrainedSockets</b>
				is enabled the receive and transmit buffers for all sockets
				will be set to this limit. Must be a value between 2048 and
				262144, in 1024 byte increments. Default of 8192 is
				recommended.</p>',
		'ControlPort' => '<p class="tor_option_description"><b>ControlPort</b>
				<i>PORT</i>|<b>unix:</b><i>path</i>|<b>auto</b>
				[<i>flags</i>]</p><p class="tor_option_description_indented">If set, Tor will accept
				connections on this port and allow those connections to
				control the Tor process using the Tor Control Protocol
				(described in control−spec.txt). Note: unless you also
				specify one or more of <b>HashedControlPassword</b> or
				<b>CookieAuthentication</b>, setting this option will cause
				Tor to allow any process on the local host to control it.
				(Setting both authentication methods means either method is
				sufficient to authenticate to Tor.) This option is required
				for many Tor controllers; most use the value of 9051. Set it
				to "auto" to have Tor pick a port for you.
				(Default: 0)</p>',
		'ControlListenAddress' => '<p class="tor_option_description"><b>ControlListenAddress</b>
				<i>IP</i>[:<i>PORT</i>]</p><p class="tor_option_description_indented">Bind the controller listener to
				this address. If you specify a port, bind to this port
				rather than the one specified in ControlPort. We strongly
				recommend that you leave this alone unless you know what
				you’re doing, since giving attackers access to your
				control listener is really dangerous. This directive can be
				specified multiple times to bind to multiple
				addresses/ports. (Default: 127.0.0.1)</p>',
		'ControlSocket' => '<p class="tor_option_description"><b>ControlSocket</b>
				<i>Path</i></p><p class="tor_option_description_indented">Like ControlPort, but listens
				on a Unix domain socket, rather than a TCP socket. <i>0</i>
				disables ControlSocket (Unix and Unix−like systems
				only.)</p>',
		'ControlSocketsGroupWritable' => '<p class="tor_option_description"><b>ControlSocketsGroupWritable
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 0,
				don’t allow the filesystem group to read and write
				unix sockets (e.g. ControlSocket). If the option is set to
				1, make the control socket readable and writable by the
				default GID. (Default: 0)</p>',
		'HashedControlPassword' => '<p class="tor_option_description"><b>HashedControlPassword</b>
				<i>hashed_password</i></p><p class="tor_option_description_indented">Allow connections on the
				control port if they present the password whose
				one−way hash is <i>hashed_password</i>. You can
				compute the hash of a password by running "tor
				−−hash−password <i>password</i>". You
				can provide several acceptable passwords by using more than
				one HashedControlPassword line.</p>',
		'CookieAuthentication' => '<p class="tor_option_description"><b>CookieAuthentication
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 1,
				allow connections on the control port when the connecting
				process knows the contents of a file named
				"control_auth_cookie", which Tor will create in
				its data directory. This authentication method should only
				be used on systems with good filesystem security. (Default:
				0)</p>',
		'CookieAuthFile' => '<p class="tor_option_description"><b>CookieAuthFile</b>
				<i>Path</i></p><p class="tor_option_description_indented">If set, this option overrides
				the default location and file name for Tor’s cookie
				file. (See CookieAuthentication above.)</p>',
		'CookieAuthFileGroupReadable' => '<p class="tor_option_description"><b>CookieAuthFileGroupReadable
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 0,
				don’t allow the filesystem group to read the cookie
				file. If the option is set to 1, make the cookie file
				readable by the default GID. [Making the file readable by
				other groups is not yet implemented; let us know if you need
				this for some reason.] (Default: 0)</p>',
		'ControlPortWriteToFile' => '<p class="tor_option_description"><b>ControlPortWriteToFile</b>
				<i>Path</i></p><p class="tor_option_description_indented">If set, Tor writes the address
				and port of any control port it opens to this address.
				Usable by controllers to learn the actual control port when
				ControlPort is set to "auto".</p>',
		'ControlPortFileGroupReadable' => '<p class="tor_option_description"><b>ControlPortFileGroupReadable
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 0,
				don’t allow the filesystem group to read the control
				port file. If the option is set to 1, make the control port
				file readable by the default GID. (Default: 0)</p>',
		'DataDirectory' => '<p class="tor_option_description"><b>DataDirectory</b>
				<i>DIR</i></p><p class="tor_option_description_indented">Store working data in DIR
				(Default: /var/lib/tor)</p>',
		'FallbackDir' => '<p class="tor_option_description"><b>FallbackDir</b>
				<i>address</i>:<i>port</i> orport=<i>port</i>
				id=<i>fingerprint</i> [weight=<i>num</i>]</p><p class="tor_option_description_indented">When we’re unable to
				connect to any directory cache for directory info (usually
				because we don’t know about any yet) we try a
				FallbackDir. By default, the directory authorities are also
				FallbackDirs.</p>',
		'DirAuthority' => '<p class="tor_option_description"><b>DirAuthority</b>
				[<i>nickname</i>] [<b>flags</b>] <i>address</i>:<i>port
				fingerprint</i></p><p class="tor_option_description_indented">Use a nonstandard authoritative
				directory server at the provided address and port, with the
				specified key fingerprint. This option can be repeated many
				times, for multiple authoritative directory servers. Flags
				are separated by spaces, and determine what kind of an
				authority this directory is. By default, an authority is not
				authoritative for any directory style or version unless an
				appropriate flag is given. Tor will use this authority as a
				bridge authoritative directory if the "bridge"
				flag is set. If a flag "orport=<b>port</b>" is
				given, Tor will use the given port when opening encrypted
				tunnels to the dirserver. If a flag
				"weight=<b>num</b>" is given, then the directory
				server is chosen randomly with probability proportional to
				that weight (default 1.0). Lastly, if a flag
				"v3ident=<b>fp</b>" is given, the dirserver is a
				v3 directory authority whose v3 long−term signing key
				has the fingerprint <b>fp</b>.</p><p class="tor_option_description_indented">If no
				<b>DirAuthority</b> line is given, Tor will use the default
				directory authorities. NOTE: this option is intended for
				setting up a private Tor network with its own directory
				authorities. If you use it, you will be distinguishable from
				other users, because you won’t believe the same
				authorities they do.</p>',
		'DirAuthorityFallbackRate' => '<p class="tor_option_description"><b>DirAuthorityFallbackRate</b>
				<i>NUM</i></p><p class="tor_option_description_indented">When configured to use both
				directory authorities and fallback directories, the
				directory authorities also work as fallbacks. They are
				chosen with their regular weights, multiplied by this
				number, which should be 1.0 or less. (Default: 1.0)</p>',
		'AlternateDirAuthority' => '<p class="tor_option_description"><b>AlternateDirAuthority</b>
				[<i>nickname</i>] [<b>flags</b>] <i>address</i>:<i>port
				fingerprint</i></p>',
		'AlternateBridgeAuthority' => '<p class="tor_option_description"><b>AlternateBridgeAuthority</b>
				[<i>nickname</i>] [<b>flags</b>] <i>address</i>:<i>port
				fingerprint</i></p><p class="tor_option_description_indented">These options behave as
				DirAuthority, but they replace fewer of the default
				directory authorities. Using AlternateDirAuthority replaces
				the default Tor directory authorities, but leaves the
				default bridge authorities in place. Similarly,
				AlternateBridgeAuthority replaces the default bridge
				authority, but leaves the directory authorities alone.</p>',
		'DisableAllSwap' => '<p class="tor_option_description"><b>DisableAllSwap
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will attempt
				to lock all current and future memory pages, so that memory
				cannot be paged out. Windows, OS X and Solaris are currently
				not supported. We believe that this feature works on modern
				Gnu/Linux distributions, and that it should work on *BSD
				systems (untested). This option requires that you start your
				Tor as root, and you should use the <b>User</b> option to
				properly reduce Tor’s privileges. (Default: 0)</p>',
		'DisableDebuggerAttachment' => '<p class="tor_option_description"><b>DisableDebuggerAttachment
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will attempt
				to prevent basic debugging attachment attempts by other
				processes. This may also keep Tor from generating core files
				if it crashes. It has no impact for users who wish to attach
				if they have CAP_SYS_PTRACE or if they are root. We believe
				that this feature works on modern Gnu/Linux distributions,
				and that it may also work on *BSD systems (untested). Some
				modern Gnu/Linux systems such as Ubuntu have the
				kernel.yama.ptrace_scope sysctl and by default enable it as
				an attempt to limit the PTRACE scope for all user processes
				by default. This feature will attempt to limit the PTRACE
				scope for Tor specifically − it will not attempt to
				alter the system wide ptrace scope as it may not even exist.
				If you wish to attach to Tor with a debugger such as gdb or
				strace you will want to set this to 0 for the duration of
				your debugging. Normal users should leave it on. Disabling
				this option while Tor is running is prohibited. (Default:
				1)</p>',
		'FetchDirInfoEarly' => '<p class="tor_option_description"><b>FetchDirInfoEarly
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will always
				fetch directory information like other directory caches,
				even if you don’t meet the normal criteria for
				fetching early. Normal users should leave it off. (Default:
				0)</p>',
		'FetchDirInfoExtraEarly' => '<p class="tor_option_description"><b>FetchDirInfoExtraEarly
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will fetch
				directory information before other directory caches. It will
				attempt to download directory information closer to the
				start of the consensus period. Normal users should leave it
				off. (Default: 0)</p>',
		'FetchHidServDescriptors' => '<p class="tor_option_description"><b>FetchHidServDescriptors
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 0, Tor will never
				fetch any hidden service descriptors from the rendezvous
				directories. This option is only useful if you’re
				using a Tor controller that handles hidden service fetches
				for you. (Default: 1)</p>',
		'FetchServerDescriptors' => '<p class="tor_option_description"><b>FetchServerDescriptors
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 0, Tor will never
				fetch any network status summaries or server descriptors
				from the directory servers. This option is only useful if
				you’re using a Tor controller that handles directory
				fetches for you. (Default: 1)</p>',
		'FetchUselessDescriptors' => '<p class="tor_option_description"><b>FetchUselessDescriptors
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will fetch
				every non−obsolete descriptor from the authorities
				that it hears about. Otherwise, it will avoid fetching
				useless descriptors, for example for routers that are not
				running. This option is useful if you’re using the
				contributed "exitlist" script to enumerate Tor
				nodes that exit to certain addresses. (Default: 0)</p>',
		'HTTPProxy' => '<p class="tor_option_description"><b>HTTPProxy</b>
				<i>host</i>[:<i>port</i>]</p><p class="tor_option_description_indented">Tor will make all its directory
				requests through this host:port (or host:80 if port is not
				specified), rather than connecting directly to any directory
				servers.</p>',
		'HTTPProxyAuthenticator' => '<p class="tor_option_description"><b>HTTPProxyAuthenticator</b>
				<i>username:password</i></p><p class="tor_option_description_indented">If defined, Tor will use this
				username:password for Basic HTTP proxy authentication, as in
				RFC 2617. This is currently the only form of HTTP proxy
				authentication that Tor supports; feel free to submit a
				patch if you want it to support others.</p>',
		'HTTPSProxy' => '<p class="tor_option_description"><b>HTTPSProxy</b>
				<i>host</i>[:<i>port</i>]</p><p class="tor_option_description_indented">Tor will make all its OR (SSL)
				connections through this host:port (or host:443 if port is
				not specified), via HTTP CONNECT rather than connecting
				directly to servers. You may want to set
				<b>FascistFirewall</b> to restrict the set of ports you
				might try to connect to, if your HTTPS proxy only allows
				connecting to certain ports.</p>',
		'HTTPSProxyAuthenticator' => '<p class="tor_option_description"><b>HTTPSProxyAuthenticator</b>
				<i>username:password</i></p><p class="tor_option_description_indented">If defined, Tor will use this
				username:password for Basic HTTPS proxy authentication, as
				in RFC 2617. This is currently the only form of HTTPS proxy
				authentication that Tor supports; feel free to submit a
				patch if you want it to support others.</p>',
		'Sandbox' => '<p class="tor_option_description"><b>Sandbox
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will run
				securely through the use of a syscall sandbox. Otherwise the
				sandbox will be disabled. The option is currently an
				experimental feature. (Default: 0)</p>',
		'Socks4Proxy' => '<p class="tor_option_description"><b>Socks4Proxy</b>
				<i>host</i>[:<i>port</i>]</p><p class="tor_option_description_indented">Tor will make all OR
				connections through the SOCKS 4 proxy at host:port (or
				host:1080 if port is not specified).</p>',
		'Socks5Proxy' => '<p class="tor_option_description"><b>Socks5Proxy</b>
				<i>host</i>[:<i>port</i>]</p><p class="tor_option_description_indented">Tor will make all OR
				connections through the SOCKS 5 proxy at host:port (or
				host:1080 if port is not specified).</p>',
		'Socks5ProxyUsername' => '<p class="tor_option_description"><b>Socks5ProxyUsername</b>
				<i>username</i></p>',
		'Socks5ProxyPassword' => '<p class="tor_option_description"><b>Socks5ProxyPassword</b>
				<i>password</i></p><p class="tor_option_description_indented">If defined, authenticate to the
				SOCKS 5 server using username and password in accordance to
				RFC 1929. Both username and password must be between 1 and
				255 characters.</p>',
		'SocksSocketsGroupWritable' => '<p class="tor_option_description"><b>SocksSocketsGroupWritable
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 0,
				don’t allow the filesystem group to read and write
				unix sockets (e.g. SocksSocket). If the option is set to 1,
				make the SocksSocket socket readable and writable by the
				default GID. (Default: 0)</p>',
		'KeepalivePeriod' => '<p class="tor_option_description"><b>KeepalivePeriod</b>
				<i>NUM</i></p><p class="tor_option_description_indented">To keep firewalls from expiring
				connections, send a padding keepalive cell every NUM seconds
				on open connections that are in use. If the connection has
				no open circuits, it will instead be closed after NUM
				seconds of idleness. (Default: 5 minutes)</p>',
		'Log' => '<p class="tor_option_description"><b>Log</b>
				<i>minSeverity</i>[−<i>maxSeverity</i>] <b>file</b>
				<i>FILENAME</i></p><p class="tor_option_description_indented">As above, but send log messages
				to the listed filename. The "Log" option may
				appear more than once in a configuration file. Messages are
				sent to all the logs that match their severity level.</p>',
		'Log' => '<p style="margin-left:11%; margin-top: 1em"><b>Log</b>
				<i>minSeverity</i>[&minus;<i>maxSeverity</i>]
				<b>stderr</b>|<b>stdout</b>|<b>syslog</b></p>
				<p style="margin-left:17%;">Send all messages between
				<i>minSeverity</i> and <i>maxSeverity</i> to the standard
				output stream, the standard error stream, or to the system
				log. (The &quot;syslog&quot; value is only supported on
				Unix.) Recognized severity levels are debug, info, notice,
				warn, and err. We advise using &quot;notice&quot; in most
				cases, since anything more verbose may provide sensitive
				information to an attacker who obtains the logs. If only one
				severity level is given, all messages of that level or
				higher will be sent to the listed destination.</p>
				<p style="margin-left:11%; margin-top: 1em"><b>Log</b>
				<i>minSeverity</i>[&minus;<i>maxSeverity</i>] <b>file</b>
				<i>FILENAME</i></p>
				<p style="margin-left:17%;">As above, but send log messages
				to the listed filename. The &quot;Log&quot; option may
				appear more than once in a configuration file. Messages are
				sent to all the logs that match their severity level.</p>
				<p style="margin-left:11%; margin-top: 1em"><b>Log
				[</b><i>domain</i>,...<b>]</b><i>minSeverity</i>[&minus;<i>maxSeverity</i>]
				... <b>file</b> <i>FILENAME</i></p>
				<p style="margin-left:11%; margin-top: 1em"><b>Log
				[</b><i>domain</i>,...<b>]</b><i>minSeverity</i>[&minus;<i>maxSeverity</i>]
				... <b>stderr</b>|<b>stdout</b>|<b>syslog</b></p>
				<p style="margin-left:17%;">As above, but select messages
				by range of log severity <i>and</i> by a set of
				&quot;logging domains&quot;. Each logging domain corresponds
				to an area of functionality inside Tor. You can specify any
				number of severity ranges for a single log statement, each
				of them prefixed by a comma&minus;separated list of logging
				domains. You can prefix a domain with ~ to indicate
				negation, and use * to indicate &quot;all domains&quot;. If
				you specify a severity range without a list of domains, it
				matches all domains.</p>
				<p style="margin-left:17%; margin-top: 1em">This is an
				advanced feature which is most useful for debugging one or
				two of Tor&rsquo;s subsystems at a time.</p>
				<p style="margin-left:17%; margin-top: 1em">The currently
				recognized domains are: general, crypto, net, config, fs,
				protocol, mm, http, app, control, circ, rend, bug, dir,
				dirserv, or, edge, acct, hist, and handshake. Domain names
				are case&minus;insensitive.</p>
				<p style="margin-left:17%; margin-top: 1em">For example,
				&quot;Log [handshake]debug [~net,~mm]info notice
				stdout&quot; sends to stdout: all handshake messages of any
				severity, all info&minus;and&minus;higher messages from
				domains other than networking and memory management, and all
				messages of severity notice or higher.</p>',
		'LogMessageDomains' => '<p class="tor_option_description"><b>LogMessageDomains
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor includes message
				domains with each log message. Every log message currently
				has at least one domain; most currently have exactly one.
				This doesn’t affect controller log messages. (Default:
				0)</p>',
		'OutboundBindAddress' => '<p class="tor_option_description"><b>OutboundBindAddress</b>
				<i>IP</i></p><p class="tor_option_description_indented">Make all outbound connections
				originate from the IP address specified. This is only useful
				when you have multiple network interfaces, and you want all
				of Tor’s outgoing connections to use a single one.
				This option may be used twice, once with an IPv4 address and
				once with an IPv6 address. This setting will be ignored for
				connections to the loopback addresses (127.0.0.0/8 and
				::1).</p>',
		'PidFile' => '<p class="tor_option_description"><b>PidFile</b>
				<i>FILE</i></p><p class="tor_option_description_indented">On startup, write our PID to
				FILE. On clean shutdown, remove FILE.</p>',
		'ProtocolWarnings' => '<p class="tor_option_description"><b>ProtocolWarnings
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor will log with
				severity \'warn\' various cases of other parties not following
				the Tor specification. Otherwise, they are logged with
				severity \'info\'. (Default: 0)</p>',
		'PredictedPortsRelevanceTime' => '<p class="tor_option_description"><b>PredictedPortsRelevanceTime</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Set how long, after the client
				has made an anonymized connection to a given port, we will
				try to make sure that we build circuits to exits that
				support that port. The maximum value for this option is 1
				hour. (Default: 1 hour)</p>',
		'RunAsDaemon' => '<p class="tor_option_description"><b>RunAsDaemon
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor forks and daemonizes
				to the background. This option has no effect on Windows;
				instead you should use the −−service
				command−line option. (Default: 0)</p>',
		'LogTimeGranularity' => '<p class="tor_option_description"><b>LogTimeGranularity</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Set the resolution of
				timestamps in Tor’s logs to NUM milliseconds. NUM must
				be positive and either a divisor or a multiple of 1 second.
				Note that this option only controls the granularity written
				by Tor to a file or console log. Tor does not (for example)
				"batch up" log messages to affect times logged by
				a controller, times attached to syslog messages, or the
				mtime fields on log files. (Default: 1 second)</p>',
		'TruncateLogFile' => '<p class="tor_option_description"><b>TruncateLogFile
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor will overwrite logs
				at startup and in response to a HUP signal, instead of
				appending to them. (Default: 0)</p>',
		'SafeLogging' => '<p class="tor_option_description"><b>SafeLogging
				0</b>|<b>1</b>|<b>relay</b></p><p class="tor_option_description_indented">Tor can scrub potentially
				sensitive strings from log messages (e.g. addresses) by
				replacing them with the string [scrubbed]. This way logs can
				still be useful, but they don’t leave behind
				personally identifying information about what sites a user
				might have visited.</p><p class="tor_option_description_indented">If this option
				is set to 0, Tor will not perform any scrubbing, if it is
				set to 1, all potentially sensitive strings are replaced. If
				it is set to relay, all log messages generated when acting
				as a relay are sanitized, but all messages generated when
				acting as a client are not. (Default: 1)</p>',
		'User' => '<p class="tor_option_description"><b>User</b>
				<i>UID</i></p><p class="tor_option_description_indented">On startup, setuid to this user
				and setgid to their primary group.</p>',
		'HardwareAccel' => '<p class="tor_option_description"><b>HardwareAccel
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If non−zero, try to use
				built−in (static) crypto hardware acceleration when
				available. (Default: 0)</p>',
		'AccelName' => '<p class="tor_option_description"><b>AccelName</b>
				<i>NAME</i></p><p class="tor_option_description_indented">When using OpenSSL hardware
				crypto acceleration attempt to load the dynamic engine of
				this name. This must be used for any dynamic hardware
				engine. Names can be verified with the openssl engine
				command.</p>',
		'AccelDir' => '<p class="tor_option_description"><b>AccelDir</b>
				<i>DIR</i></p><p class="tor_option_description_indented">Specify this option if using
				dynamic hardware acceleration and the engine implementation
				library resides somewhere other than the OpenSSL
				default.</p>',
		'AvoidDiskWrites' => '<p class="tor_option_description"><b>AvoidDiskWrites
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If non−zero, try to write
				to disk less frequently than we would otherwise. This is
				useful when running on flash memory or other media that
				support only a limited number of writes. (Default: 0)</p>',
		'CircuitPriorityHalflife' => '<p class="tor_option_description"><b>CircuitPriorityHalflife</b>
				<i>NUM1</i></p><p class="tor_option_description_indented">If this value is set, we
				override the default algorithm for choosing which
				circuit’s cell to deliver or relay next. When the
				value is 0, we round−robin between the active circuits
				on a connection, delivering one cell from each in turn. When
				the value is positive, we prefer delivering cells from
				whichever connection has the lowest weighted cell count,
				where cells are weighted exponentially according to the
				supplied CircuitPriorityHalflife value (in seconds). If this
				option is not set at all, we use the behavior recommended in
				the current consensus networkstatus. This is an advanced
				option; you generally shouldn’t have to mess with it.
				(Default: not set)</p>',
		'DisableIOCP' => '<p class="tor_option_description"><b>DisableIOCP
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If Tor was built to use the
				Libevent’s "bufferevents" networking code
				and you’re running on Windows, setting this option to
				1 will tell Libevent not to use the Windows IOCP networking
				API. (Default: 1)</p>',
		'UserspaceIOCPBuffers' => '<p class="tor_option_description"><b>UserspaceIOCPBuffers
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If IOCP is enabled (see
				DisableIOCP above), setting this option to 1 will tell Tor
				to disable kernel−space TCP buffers, in order to avoid
				needless copy operations and try not to run out of
				non−paged RAM. This feature is experimental;
				don’t use it yet unless you’re eager to help
				tracking down bugs. (Default: 0)</p>',
		'UseFilteringSSLBufferevents' => '<p class="tor_option_description"><b>UseFilteringSSLBufferevents
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Tells Tor to do its SSL
				communication using a chain of bufferevents: one for SSL and
				one for networking. This option has no effect if
				bufferevents are disabled (in which case it can’t turn
				on), or if IOCP bufferevents are enabled (in which case it
				can’t turn off). This option is useful for debugging
				only; most users shouldn’t touch it. (Default: 0)</p>',
		'CountPrivateBandwidth' => '<p class="tor_option_description"><b>CountPrivateBandwidth
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set, then
				Tor’s rate−limiting applies not only to remote
				connections, but also to connections to private addresses
				like 127.0.0.1 or 10.0.0.1. This is mostly useful for
				debugging rate−limiting. (Default: 0)</p>',
		'AllowInvalidNodes' => '<p class="tor_option_description"><b>AllowInvalidNodes
				entry</b>|<b>exit</b>|<b>middle</b>|<b>introduction</b>|<b>rendezvous</b>|<b>...</b></p><p class="tor_option_description_indented">If some Tor servers are
				obviously not working right, the directory authorities can
				manually mark them as invalid, meaning that it’s not
				recommended you use them for entry or exit positions in your
				circuits. You can opt to use them in some circuit positions,
				though. The default is "middle,rendezvous", and
				other choices are not advised.</p>',
		'ExcludeSingleHopRelays' => '<p class="tor_option_description"><b>ExcludeSingleHopRelays
				0</b>|<b>1</b></p><p class="tor_option_description_indented">This option controls whether
				circuits built by Tor will include relays with the
				AllowSingleHopExits flag set to true. If
				ExcludeSingleHopRelays is set to 0, these relays will be
				included. Note that these relays might be at higher risk of
				being seized or observed, so they are not normally included.
				Also note that relatively few clients turn off this option,
				so using these relays might make your client stand out.
				(Default: 1)</p>',
		'Bridge' => '<p class="tor_option_description"><b>Bridge</b>
				[<i>transport</i>] <i>IP</i>:<i>ORPort</i>
				[<i>fingerprint</i>]</p><p class="tor_option_description_indented">When set along with UseBridges,
				instructs Tor to use the relay at "IP:ORPort" as a
				"bridge" relaying into the Tor network. If
				"fingerprint" is provided (using the same format
				as for DirAuthority), we will verify that the relay running
				at that location has the right fingerprint. We also use
				fingerprint to look up the bridge descriptor at the bridge
				authority, if it’s provided and if
				UpdateBridgesFromAuthority is set too.</p><p class="tor_option_description_indented">If
				"transport" is provided, and matches to a
				ClientTransportPlugin line, we use that pluggable transports
				proxy to transfer data to the bridge.</p>',
		'LearnCircuitBuildTimeout' => '<p class="tor_option_description"><b>LearnCircuitBuildTimeout
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 0, CircuitBuildTimeout
				adaptive learning is disabled. (Default: 1)</p>',
		'CircuitBuildTimeout' => '<p class="tor_option_description"><b>CircuitBuildTimeout</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Try for at most NUM seconds
				when building circuits. If the circuit isn’t open in
				that time, give up on it. If LearnCircuitBuildTimeout is 1,
				this value serves as the initial value to use before a
				timeout is learned. If LearnCircuitBuildTimeout is 0, this
				value is the only value used. (Default: 60 seconds)</p>',
		'CircuitIdleTimeout' => '<p class="tor_option_description"><b>CircuitIdleTimeout</b>
				<i>NUM</i></p><p class="tor_option_description_indented">If we have kept a clean (never
				used) circuit around for NUM seconds, then close it. This
				way when the Tor client is entirely idle, it can expire all
				of its circuits, and then expire its TLS connections. Also,
				if we end up making a circuit that is not useful for exiting
				any of the requests we’re receiving, it won’t
				forever take up a slot in the circuit list. (Default: 1
				hour)</p>',
		'CircuitStreamTimeout' => '<p class="tor_option_description"><b>CircuitStreamTimeout</b>
				<i>NUM</i></p><p class="tor_option_description_indented">If non−zero, this option
				overrides our internal timeout schedule for how many seconds
				until we detach a stream from a circuit and try a new
				circuit. If your network is particularly slow, you might
				want to set this to a number like 60. (Default: 0)</p>',
		'ClientOnly' => '<p class="tor_option_description"><b>ClientOnly
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will not run
				as a relay or serve directory requests, even if the ORPort,
				ExtORPort, or DirPort options are set. (This config option
				is mostly unnecessary: we added it back when we were
				considering having Tor clients auto−promote themselves
				to being relays if they were stable and fast enough. The
				current behavior is simply that Tor is a client unless
				ORPort, ExtORPort, or DirPort are configured.) (Default:
				0)</p>',
		'ExcludeNodes' => '<p class="tor_option_description"><b>ExcludeNodes</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity
				fingerprints, country codes, and address patterns of nodes
				to avoid when building a circuit. Country codes must be
				wrapped in braces; fingerprints may be preceded by a dollar
				sign. (Example: ExcludeNodes
				ABCD1234CDEF5678ABCD1234CDEF5678ABCD1234, {cc},
				255.254.0.0/8)</p><p class="tor_option_description_indented">By default,
				this option is treated as a preference that Tor is allowed
				to override in order to keep working. For example, if you
				try to connect to a hidden service, but you have excluded
				all of the hidden service’s introduction points, Tor
				will connect to one of them anyway. If you do not want this
				behavior, set the StrictNodes option (documented below).</p><p class="tor_option_description_indented">Note also that
				if you are a relay, this (and the other node selection
				options below) only affects your own circuits that Tor
				builds for you. Clients can still build circuits through you
				to any node. Controllers can tell Tor to build circuits
				through any node.</p><p class="tor_option_description_indented">Country codes
				are case−insensitive. The code "{??}" refers
				to nodes whose country can’t be identified. No country
				code, including {??}, works if no GeoIPFile can be loaded.
				See also the GeoIPExcludeUnknown option below.</p>',
		'ExcludeExitNodes' => '<p class="tor_option_description"><b>ExcludeExitNodes</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity
				fingerprints, country codes, and address patterns of nodes
				to never use when picking an exit
				node−−−that is, a node that delivers
				traffic for you outside the Tor network. Note that any node
				listed in ExcludeNodes is automatically considered to be
				part of this list too. See the <b>ExcludeNodes</b> option
				for more information on how to specify nodes. See also the
				caveats on the "ExitNodes" option below.</p>',
		'GeoIPExcludeUnknown' => '<p class="tor_option_description"><b>GeoIPExcludeUnknown
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">If this option is set to
				<i>auto</i>, then whenever any country code is set in
				ExcludeNodes or ExcludeExitNodes, all nodes with unknown
				country ({??} and possibly {A1}) are treated as excluded as
				well. If this option is set to <i>1</i>, then all unknown
				countries are treated as excluded in ExcludeNodes and
				ExcludeExitNodes. This option has no effect when a GeoIP
				file isn’t configured or can’t be found.
				(Default: auto)</p>',
		'ExitNodes' => '<p class="tor_option_description"><b>ExitNodes</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity
				fingerprints, country codes, and address patterns of nodes
				to use as exit node−−−that is, a node that
				delivers traffic for you outside the Tor network. See the
				<b>ExcludeNodes</b> option for more information on how to
				specify nodes.</p><p class="tor_option_description_indented">Note that if
				you list too few nodes here, or if you exclude too many exit
				nodes with ExcludeExitNodes, you can degrade functionality.
				For example, if none of the exits you list allows traffic on
				port 80 or 443, you won’t be able to browse the
				web.</p><p class="tor_option_description_indented">Note also that
				not every circuit is used to deliver traffic outside of the
				Tor network. It is normal to see non−exit circuits
				(such as those used to connect to hidden services, those
				that do directory fetches, those used for relay reachability
				self−tests, and so on) that end at a non−exit
				node. To keep a node from being used entirely, see
				ExcludeNodes and StrictNodes.</p><p class="tor_option_description_indented">The
				ExcludeNodes option overrides this option: any node listed
				in both ExitNodes and ExcludeNodes is treated as
				excluded.</p><p class="tor_option_description_indented">The .exit
				address notation, if enabled via AllowDotExit, overrides
				this option.</p>',
		'EntryNodes' => '<p class="tor_option_description"><b>EntryNodes</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity fingerprints
				and country codes of nodes to use for the first hop in your
				normal circuits. Normal circuits include all circuits except
				for direct connections to directory servers. The Bridge
				option overrides this option; if you have configured bridges
				and UseBridges is 1, the Bridges are used as your entry
				nodes.</p><p class="tor_option_description_indented">The
				ExcludeNodes option overrides this option: any node listed
				in both EntryNodes and ExcludeNodes is treated as excluded.
				See the <b>ExcludeNodes</b> option for more information on
				how to specify nodes.</p>',
		'StrictNodes' => '<p class="tor_option_description"><b>StrictNodes
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If StrictNodes is set to 1, Tor
				will treat the ExcludeNodes option as a requirement to
				follow for all the circuits you generate, even if doing so
				will break functionality for you. If StrictNodes is set to
				0, Tor will still try to avoid nodes in the ExcludeNodes
				list, but it will err on the side of avoiding unexpected
				errors. Specifically, StrictNodes 0 tells Tor that it is
				okay to use an excluded node when it is <b>necessary</b> to
				perform relay reachability self−tests, connect to a
				hidden service, provide a hidden service to a client,
				fulfill a .exit request, upload directory information, or
				download directory information. (Default: 0)</p>',
		'FascistFirewall' => '<p class="tor_option_description"><b>FascistFirewall
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor will only create
				outgoing connections to ORs running on ports that your
				firewall allows (defaults to 80 and 443; see
				<b>FirewallPorts</b>). This will allow you to run Tor as a
				client behind a firewall with restrictive policies, but will
				not allow you to run as a server behind such a firewall. If
				you prefer more fine−grained control, use
				ReachableAddresses instead.</p>',
		'FirewallPorts' => '<p class="tor_option_description"><b>FirewallPorts</b>
				<i>PORTS</i></p><p class="tor_option_description_indented">A list of ports that your
				firewall allows you to connect to. Only used when
				<b>FascistFirewall</b> is set. This option is deprecated;
				use ReachableAddresses instead. (Default: 80, 443)</p>',
		'ReachableAddresses' => '<p class="tor_option_description"><b>ReachableAddresses</b>
				<i>ADDR</i>[/<i>MASK</i>][:<i>PORT</i>]...</p><p class="tor_option_description_indented">A comma−separated list of
				IP addresses and ports that your firewall allows you to
				connect to. The format is as for the addresses in
				ExitPolicy, except that "accept" is understood
				unless "reject" is explicitly provided. For
				example, \'ReachableAddresses 99.0.0.0/8, reject
				18.0.0.0/8:80, accept *:80\' means that your firewall allows
				connections to everything inside net 99, rejects port 80
				connections to net 18, and accepts connections to port 80
				otherwise. (Default: \'accept *:*\'.)</p>',
		'ReachableDirAddresses' => '<p class="tor_option_description"><b>ReachableDirAddresses</b>
				<i>ADDR</i>[/<i>MASK</i>][:<i>PORT</i>]...</p><p class="tor_option_description_indented">Like <b>ReachableAddresses</b>,
				a list of addresses and ports. Tor will obey these
				restrictions when fetching directory information, using
				standard HTTP GET requests. If not set explicitly then the
				value of <b>ReachableAddresses</b> is used. If
				<b>HTTPProxy</b> is set then these connections will go
				through that proxy.</p>',
		'ReachableORAddresses' => '<p class="tor_option_description"><b>ReachableORAddresses</b>
				<i>ADDR</i>[/<i>MASK</i>][:<i>PORT</i>]...</p><p class="tor_option_description_indented">Like <b>ReachableAddresses</b>,
				a list of addresses and ports. Tor will obey these
				restrictions when connecting to Onion Routers, using
				TLS/SSL. If not set explicitly then the value of
				<b>ReachableAddresses</b> is used. If <b>HTTPSProxy</b> is
				set then these connections will go through that proxy.</p><p class="tor_option_description_indented">The separation
				between <b>ReachableORAddresses</b> and
				<b>ReachableDirAddresses</b> is only interesting when you
				are connecting through proxies (see <b>HTTPProxy</b> and
				<b>HTTPSProxy</b>). Most proxies limit TLS connections
				(which Tor uses to connect to Onion Routers) to port 443,
				and some limit HTTP GET requests (which Tor uses for
				fetching directory information) to port 80.</p>',
		'HidServAuth' => '<p class="tor_option_description"><b>HidServAuth</b>
				<i>onion−address auth−cookie</i>
				[<i>service−name</i>]</p><p class="tor_option_description_indented">Client authorization for a
				hidden service. Valid onion addresses contain 16 characters
				in a−z2−7 plus ".onion", and valid
				auth cookies contain 22 characters in
				A−Za−z0−9+/. The service name is only used
				for internal purposes, e.g., for Tor controllers. This
				option may be used multiple times for different hidden
				services. If a hidden service uses authorization and this
				option is not set, the hidden service is not accessible.
				Hidden services can be configured to require authorization
				using the <b>HiddenServiceAuthorizeClient</b> option.</p>',
		'CloseHSClientCircuitsImmediatelyOnTimeout' => '<p class="tor_option_description"><b>CloseHSClientCircuitsImmediatelyOnTimeout
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor will close unfinished
				hidden service client circuits which have not moved closer
				to connecting to their destination hidden service when their
				internal state has not changed for the duration of the
				current circuit−build timeout. Otherwise, such
				circuits will be left open, in the hope that they will
				finish connecting to their destination hidden services. In
				either case, another set of introduction and rendezvous
				circuits for the same destination hidden service will be
				launched. (Default: 0)</p>',
		'CloseHSServiceRendCircuitsImmediatelyOnTimeout' => '<p class="tor_option_description"><b>CloseHSServiceRendCircuitsImmediatelyOnTimeout
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor will close unfinished
				hidden−service−side rendezvous circuits after
				the current circuit−build timeout. Otherwise, such
				circuits will be left open, in the hope that they will
				finish connecting to their destinations. In either case,
				another rendezvous circuit for the same destination client
				will be launched. (Default: 0)</p>',
		'LongLivedPorts' => '<p class="tor_option_description"><b>LongLivedPorts</b>
				<i>PORTS</i></p><p class="tor_option_description_indented">A list of ports for services
				that tend to have long−running connections (e.g. chat
				and interactive shells). Circuits for streams that use these
				ports will contain only high−uptime nodes, to reduce
				the chance that a node will go down before the stream is
				finished. Note that the list is also honored for circuits
				(both client and service side) involving hidden services
				whose virtual port is in this list. (Default: 21, 22, 706,
				1863, 5050, 5190, 5222, 5223, 6523, 6667, 6697, 8300)</p>',
		'MapAddress' => '<p class="tor_option_description"><b>MapAddress</b>
				<i>address newaddress</i></p><p class="tor_option_description_indented">When a request for address
				arrives to Tor, it will transform to newaddress before
				processing it. For example, if you always want connections
				to www.example.com to exit via <i>torserver</i> (where
				<i>torserver</i> is the nickname of the server), use
				"MapAddress www.example.com
				www.example.com.torserver.exit". If the value is
				prefixed with a "*.", matches an entire domain.
				For example, if you always want connections to example.com
				and any if its subdomains to exit via <i>torserver</i>
				(where <i>torserver</i> is the nickname of the server), use
				"MapAddress *.example.com
				*.example.com.torserver.exit". (Note the leading
				"*." in each part of the directive.) You can also
				redirect all subdomains of a domain to a single address. For
				example, "MapAddress *.example.com
				www.example.com".</p><p class="tor_option_description_indented">NOTES:</p><p class="tor_option_description_indented2">1. When
				evaluating MapAddress expressions Tor stops when it hits the
				most recently added expression that matches the requested
				address. So if you have the following in your torrc,
				www.torproject.org will map to 1.1.1.1:</p><p class="tor_option_description_indented3">MapAddress
				www.torproject.org 2.2.2.2 <br>
				MapAddress www.torproject.org 1.1.1.1</p><p class="tor_option_description_indented2">2. Tor
				evaluates the MapAddress configuration until it finds no
				matches. So if you have the following in your torrc,
				www.torproject.org will map to 2.2.2.2:</p><p class="tor_option_description_indented3">MapAddress
				1.1.1.1 2.2.2.2 <br>
				MapAddress www.torproject.org 1.1.1.1</p><p class="tor_option_description_indented2">3. The
				following MapAddress expression is invalid (and will be
				ignored) because you cannot map from a specific address to a
				wildcard address:</p><p class="tor_option_description_indented3">MapAddress
				www.torproject.org *.torproject.org.torserver.exit</p><p class="tor_option_description_indented2">4. Using a
				wildcard to match only part of a string (as in *ample.com)
				is also invalid.</p>',
		'NewCircuitPeriod' => '<p class="tor_option_description"><b>NewCircuitPeriod</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Every NUM seconds consider
				whether to build a new circuit. (Default: 30 seconds)</p>',
		'MaxCircuitDirtiness' => '<p class="tor_option_description"><b>MaxCircuitDirtiness</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Feel free to reuse a circuit
				that was first used at most NUM seconds ago, but never
				attach a new stream to a circuit that is too old. For hidden
				services, this applies to the <i>last</i> time a circuit was
				used, not the first. Circuits with streams constructed with
				SOCKS authentication via SocksPorts that have
				<b>KeepAliveIsolateSOCKSAuth</b> ignore this value.
				(Default: 10 minutes)</p>',
		'MaxClientCircuitsPending' => '<p class="tor_option_description"><b>MaxClientCircuitsPending</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Do not allow more than NUM
				circuits to be pending at a time for handling client
				streams. A circuit is pending if we have begun constructing
				it, but it has not yet been completely constructed.
				(Default: 32)</p>',
		'NodeFamily' => '<p class="tor_option_description"><b>NodeFamily</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">The Tor servers, defined by
				their identity fingerprints, constitute a "family"
				of similar or co−administered servers, so never use
				any two of them in the same circuit. Defining a NodeFamily
				is only needed when a server doesn’t list the family
				itself (with MyFamily). This option can be used multiple
				times; each instance defines a separate family. In addition
				to nodes, you can also list IP address and ranges and
				country codes in {curly braces}. See the <b>ExcludeNodes</b>
				option for more information on how to specify nodes.</p>',
		'EnforceDistinctSubnets' => '<p class="tor_option_description"><b>EnforceDistinctSubnets
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If 1, Tor will not put two
				servers whose IP addresses are "too close" on the
				same circuit. Currently, two addresses are "too
				close" if they lie in the same /16 range. (Default:
				1)</p>',
		'SOCKSPort' => '<p class="tor_option_description"><b>SOCKSPort</b>
				[<i>address</i>:]<i>port</i>|<b>unix:</b><i>path</i>|<b>auto</b>
				[<i>flags</i>] [<i>isolation flags</i>]</p><p class="tor_option_description_indented">Open this port to listen for
				connections from SOCKS−speaking applications. Set this
				to 0 if you don’t want to allow application
				connections via SOCKS. Set it to "auto" to have
				Tor pick a port for you. This directive can be specified
				multiple times to bind to multiple addresses/ports.
				(Default: 9050)</p><p class="tor_option_description_indented">NOTE: Although
				this option allows you to specify an IP address other than
				localhost, you should do so only with extreme caution. The
				SOCKS protocol is unencrypted and (as we use it)
				unauthenticated, so exposing it in this way could leak your
				information to anybody watching your network, and allow
				anybody to use your computer as an open proxy.</p><p class="tor_option_description_indented">The
				<i>isolation flags</i> arguments give Tor rules for which
				streams received on this SOCKSPort are allowed to share
				circuits with one another. Recognized isolation flags
				are:</p><p class="tor_option_description_indented"><b>IsolateClientAddr</b></p><p class="tor_option_description_indented2">Don’t share circuits with
				streams from a different client address. (On by default and
				strongly recommended; you can disable it with
				<b>NoIsolateClientAddr</b>.)</p><p class="tor_option_description_indented"><b>IsolateSOCKSAuth</b></p><p class="tor_option_description_indented2">Don’t share circuits with
				streams for which different SOCKS authentication was
				provided. (On by default; you can disable it with
				<b>NoIsolateSOCKSAuth</b>.)</p><p class="tor_option_description_indented"><b>IsolateClientProtocol</b></p><p class="tor_option_description_indented2">Don’t share circuits with
				streams using a different protocol. (SOCKS 4, SOCKS 5,
				TransPort connections, NATDPort connections, and DNSPort
				requests are all considered to be different protocols.)</p><p class="tor_option_description_indented"><b>IsolateDestPort</b></p><p class="tor_option_description_indented2">Don’t share circuits with
				streams targeting a different destination port.</p><p class="tor_option_description_indented"><b>IsolateDestAddr</b></p><p class="tor_option_description_indented2">Don’t share circuits with
				streams targeting a different destination address.</p><p class="tor_option_description_indented"><b>KeepAliveIsolateSOCKSAuth</b></p><p class="tor_option_description_indented2">If <b>IsolateSOCKSAuth</b> is
				enabled, keep alive circuits that have streams with SOCKS
				authentication set indefinitely.</p><p class="tor_option_description_indented"><b>SessionGroup=</b><i>INT</i></p><p class="tor_option_description_indented2">If no other isolation rules
				would prevent it, allow streams on this port to share
				circuits with streams from every other port with the same
				session group. (By default, streams received on different
				SOCKSPorts, TransPorts, etc are always isolated from one
				another. This option overrides that behavior.)</p><p class="tor_option_description_indented">Other
				recognized <i>flags</i> for a SOCKSPort are:</p><p class="tor_option_description_indented"><b>NoIPv4Traffic</b></p><p class="tor_option_description_indented2">Tell exits to not connect to
				IPv4 addresses in response to SOCKS requests on this
				connection.</p><p class="tor_option_description_indented"><b>IPv6Traffic</b></p><p class="tor_option_description_indented2">Tell exits to allow IPv6
				addresses in response to SOCKS requests on this connection,
				so long as SOCKS5 is in use. (SOCKS4 can’t handle
				IPv6.)</p><p class="tor_option_description_indented"><b>PreferIPv6</b></p><p class="tor_option_description_indented2">Tells exits that, if a host has
				both an IPv4 and an IPv6 address, we would prefer to connect
				to it via IPv6. (IPv4 is the default.)</p><p class="tor_option_description_indented"><b>CacheIPv4DNS</b></p><p class="tor_option_description_indented2">Tells the client to remember
				IPv4 DNS answers we receive from exit nodes via this
				connection. (On by default.)</p><p class="tor_option_description_indented"><b>CacheIPv6DNS</b></p><p class="tor_option_description_indented2">Tells the client to remember
				IPv6 DNS answers we receive from exit nodes via this
				connection.</p><p class="tor_option_description_indented"><b>GroupWritable</b></p><p class="tor_option_description_indented2">Unix domain sockets only: makes
				the socket get created as group−writable.</p><p class="tor_option_description_indented"><b>WorldWritable</b></p><p class="tor_option_description_indented2">Unix domain sockets only: makes
				the socket get created as world−writable.</p><p class="tor_option_description_indented"><b>CacheDNS</b></p><p class="tor_option_description_indented2">Tells the client to remember
				all DNS answers we receive from exit nodes via this
				connection.</p><p class="tor_option_description_indented"><b>UseIPv4Cache</b></p><p class="tor_option_description_indented2">Tells the client to use any
				cached IPv4 DNS answers we have when making requests via
				this connection. (NOTE: This option, along UseIPv6Cache and
				UseDNSCache, can harm your anonymity, and probably
				won’t help performance as much as you might expect.
				Use with care!)</p><p class="tor_option_description_indented"><b>UseIPv6Cache</b></p><p class="tor_option_description_indented2">Tells the client to use any
				cached IPv6 DNS answers we have when making requests via
				this connection.</p><p class="tor_option_description_indented"><b>UseDNSCache</b></p><p class="tor_option_description_indented2">Tells the client to use any
				cached DNS answers we have when making requests via this
				connection.</p><p class="tor_option_description_indented"><b>PreferIPv6Automap</b></p><p class="tor_option_description_indented2">When serving a hostname lookup
				request on this port that should get automapped (according
				to AutomapHostsOnResolve), if we could return either an IPv4
				or an IPv6 answer, prefer an IPv6 answer. (On by
				default.)</p><p class="tor_option_description_indented"><b>PreferSOCKSNoAuth</b></p><p class="tor_option_description_indented2">Ordinarily, when an application
				offers both "username/password authentication" and
				"no authentication" to Tor via SOCKS5, Tor selects
				username/password authentication so that IsolateSOCKSAuth
				can work. This can confuse some applications, if they offer
				a username/password combination then get confused when asked
				for one. You can disable this behavior, so that Tor will
				select "No authentication" when IsolateSOCKSAuth
				is disabled, or when this option is set.</p>',
		'SOCKSListenAddress' => '<p class="tor_option_description"><b>SOCKSListenAddress</b>
				<i>IP</i>[:<i>PORT</i>]</p><p class="tor_option_description_indented">Bind to this address to listen
				for connections from Socks−speaking applications.
				(Default: 127.0.0.1) You can also specify a port (e.g.
				192.168.0.1:9100). This directive can be specified multiple
				times to bind to multiple addresses/ports. (DEPRECATED: As
				of 0.2.3.x−alpha, you can now use multiple SOCKSPort
				entries, and provide addresses for SOCKSPort entries, so
				SOCKSListenAddress no longer has a purpose. For backward
				compatibility, SOCKSListenAddress is only allowed when
				SOCKSPort is just a port number.)</p>',
		'SocksPolicy' => '<p class="tor_option_description"><b>SocksPolicy</b>
				<i>policy</i>,<i>policy</i>,<i>...</i></p><p class="tor_option_description_indented">Set an entrance policy for this
				server, to limit who can connect to the SocksPort and
				DNSPort ports. The policies have the same form as exit
				policies below, except that port specifiers are ignored. Any
				address not matched by some entry in the policy is
				accepted.</p>',
		'SocksTimeout' => '<p class="tor_option_description"><b>SocksTimeout</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Let a socks connection wait NUM
				seconds handshaking, and NUM seconds unattached waiting for
				an appropriate circuit, before we fail it. (Default: 2
				minutes)</p>',
		'TokenBucketRefillInterval' => '<p class="tor_option_description"><b>TokenBucketRefillInterval</b>
				<i>NUM</i> [<b>msec</b>|<b>second</b>]</p><p class="tor_option_description_indented">Set the refill interval of
				Tor’s token bucket to NUM milliseconds. NUM must be
				between 1 and 1000, inclusive. Note that the configured
				bandwidth limits are still expressed in bytes per second:
				this option only affects the frequency with which Tor checks
				to see whether previously exhausted connections may read
				again. (Default: 100 msec)</p>',
		'TrackHostExits' => '<p class="tor_option_description"><b>TrackHostExits</b>
				<i>host</i>,<i>.domain</i>,<i>...</i></p><p class="tor_option_description_indented">For each value in the comma
				separated list, Tor will track recent connections to hosts
				that match this value and attempt to reuse the same exit
				node for each. If the value is prepended with a \'.\', it is
				treated as matching an entire domain. If one of the values
				is just a \'.\', it means match everything. This option is
				useful if you frequently connect to sites that will expire
				all your authentication cookies (i.e. log you out) if your
				IP address changes. Note that this option does have the
				disadvantage of making it more clear that a given history is
				associated with a single user. However, most people who
				would wish to observe this will observe it through cookies
				or other protocol−specific means anyhow.</p>',
		'TrackHostExitsExpire' => '<p class="tor_option_description"><b>TrackHostExitsExpire</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Since exit servers go up and
				down, it is desirable to expire the association between host
				and exit server after NUM seconds. The default is 1800
				seconds (30 minutes).</p>',
		'UpdateBridgesFromAuthority' => '<p class="tor_option_description"><b>UpdateBridgesFromAuthority
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When set (along with
				UseBridges), Tor will try to fetch bridge descriptors from
				the configured bridge authorities when feasible. It will
				fall back to a direct request if the authority responds with
				a 404. (Default: 0)</p>',
		'UseBridges' => '<p class="tor_option_description"><b>UseBridges
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When set, Tor will fetch
				descriptors for each bridge listed in the "Bridge"
				config lines, and use these relays as both entry guards and
				directory guards. (Default: 0)</p>',
		'UseEntryGuards' => '<p class="tor_option_description"><b>UseEntryGuards
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 1, we
				pick a few long−term entry servers, and try to stick
				with them. This is desirable because constantly changing
				servers increases the odds that an adversary who owns some
				servers will observe a fraction of your paths. (Default:
				1)</p>',
		'UseEntryGuardsAsDirGuards' => '<p class="tor_option_description"><b>UseEntryGuardsAsDirGuards
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 1, and
				UseEntryGuards is also set to 1, we try to use our entry
				guards as directory guards, and failing that, pick more
				nodes to act as our directory guards. This helps prevent an
				adversary from enumerating clients. It’s only
				available for clients (non−relay, non−bridge)
				that aren’t configured to download any
				non−default directory material. It doesn’t
				currently do anything when we lack a live consensus.
				(Default: 1)</p>',
		'GuardfractionFile' => '<p class="tor_option_description"><b>GuardfractionFile</b>
				<i>FILENAME</i></p><p class="tor_option_description_indented">V3 authoritative directories
				only. Configures the location of the guardfraction file
				which contains information about how long relays have been
				guards. (Default: unset)</p>',
		'UseGuardFraction' => '<p class="tor_option_description"><b>UseGuardFraction
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">This torrc option specifies
				whether clients should use the guardfraction information
				found in the consensus during path selection. If it’s
				set to <i>auto</i>, clients will do what the
				UseGuardFraction consensus parameter tells them to do.
				(Default: auto)</p>',
		'NumEntryGuards' => '<p class="tor_option_description"><b>NumEntryGuards</b>
				<i>NUM</i></p><p class="tor_option_description_indented">If UseEntryGuards is set to 1,
				we will try to pick a total of NUM routers as
				long−term entries for our circuits. If NUM is 0, we
				try to learn the number from the NumEntryGuards consensus
				parameter, and default to 3 if the consensus parameter
				isn’t set. (Default: 0)</p>',
		'NumDirectoryGuards' => '<p class="tor_option_description"><b>NumDirectoryGuards</b>
				<i>NUM</i></p><p class="tor_option_description_indented">If
				UseEntryGuardsAsDirectoryGuards is enabled, we try to make
				sure we have at least NUM routers to use as directory
				guards. If this option is set to 0, use the value from the
				NumDirectoryGuards consensus parameter, falling back to the
				value from NumEntryGuards if the consensus parameter is 0 or
				isn’t set. (Default: 0)</p>',
		'GuardLifetime' => '<p class="tor_option_description"><b>GuardLifetime</b>
				<i>N</i> <b>days</b>|<b>weeks</b>|<b>months</b></p><p class="tor_option_description_indented">If nonzero, and UseEntryGuards
				is set, minimum time to keep a guard before picking a new
				one. If zero, we use the GuardLifetime parameter from the
				consensus directory. No value here may be less than 1 month
				or greater than 5 years; out−of−range values are
				clamped. (Default: 0)</p>',
		'SafeSocks' => '<p class="tor_option_description"><b>SafeSocks
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is enabled,
				Tor will reject application connections that use unsafe
				variants of the socks protocol — ones that only
				provide an IP address, meaning the application is doing a
				DNS resolve first. Specifically, these are socks4 and socks5
				when not doing remote DNS. (Default: 0)</p>',
		'TestSocks' => '<p class="tor_option_description"><b>TestSocks
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is enabled,
				Tor will make a notice−level log entry for each
				connection to the Socks port indicating whether the request
				used a safe socks protocol or an unsafe one (see above entry
				on SafeSocks). This helps to determine whether an
				application using Tor is possibly leaking DNS requests.
				(Default: 0)</p>',
		'WarnUnsafeSocks' => '<p class="tor_option_description"><b>WarnUnsafeSocks
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is enabled,
				Tor will warn whenever a request is received that only
				contains an IP address instead of a hostname. Allowing
				applications to do DNS resolves themselves is usually a bad
				idea and can leak your location to attackers. (Default:
				1)</p>',
		'VirtualAddrNetworkIPv4' => '<p class="tor_option_description"><b>VirtualAddrNetworkIPv4</b>
				<i>Address</i>/<i>bits</i></p>',
		'VirtualAddrNetworkIPv6' => '<p class="tor_option_description"><b>VirtualAddrNetworkIPv6</b>
				[<i>Address</i>]/<i>bits</i></p><p class="tor_option_description_indented">When Tor needs to assign a
				virtual (unused) address because of a MAPADDRESS command
				from the controller or the AutomapHostsOnResolve feature,
				Tor picks an unassigned address from this range. (Defaults:
				127.192.0.0/10 and [FE80::]/10 respectively.)</p><p class="tor_option_description_indented">When providing
				proxy server service to a network of computers using a tool
				like dns−proxy−tor, change the IPv4 network to
				"10.192.0.0/10" or "172.16.0.0/12" and
				change the IPv6 network to "[FC00]/7". The default
				<b>VirtualAddrNetwork</b> address ranges on a properly
				configured machine will route to the loopback or
				link−local interface. For local use, no change to the
				default VirtualAddrNetwork setting is needed.</p>',
		'AllowNonRFC953Hostnames' => '<p class="tor_option_description"><b>AllowNonRFC953Hostnames
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is disabled,
				Tor blocks hostnames containing illegal characters (like @
				and :) rather than sending them to an exit node to be
				resolved. This helps trap accidental attempts to resolve
				URLs and so on. (Default: 0)</p>',
		'AllowDotExit' => '<p class="tor_option_description"><b>AllowDotExit
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If enabled, we convert
				"www.google.com.foo.exit" addresses on the
				SocksPort/TransPort/NATDPort into "www.google.com"
				addresses that exit from the node "foo". Disabled
				by default since attacking websites and exit relays can use
				it to manipulate your path selection. (Default: 0)</p>',
		'FastFirstHopPK' => '<p class="tor_option_description"><b>FastFirstHopPK
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">When this option is disabled,
				Tor uses the public key step for the first hop of creating
				circuits. Skipping it is generally safe since we have
				already used TLS to authenticate the relay and to establish
				forward−secure keys. Turning this option off makes
				circuit building a little slower. Setting this option to
				"auto" takes advice from the authorities in the
				latest consensus about whether to use this feature.</p><p class="tor_option_description_indented">Note that Tor
				will always use the public key step for the first hop if
				it’s operating as a relay, and it will never use the
				public key step if it doesn’t yet know the onion key
				of the first hop. (Default: auto)</p>',
		'TransPort' => '<p class="tor_option_description"><b>TransPort</b>
				[<i>address</i>:]<i>port</i>|<b>auto</b> [<i>isolation
				flags</i>]</p><p class="tor_option_description_indented">Open this port to listen for
				transparent proxy connections. Set this to 0 if you
				don’t want to allow transparent proxy connections. Set
				the port to "auto" to have Tor pick a port for
				you. This directive can be specified multiple times to bind
				to multiple addresses/ports. See SOCKSPort for an
				explanation of isolation flags.</p><p class="tor_option_description_indented">TransPort
				requires OS support for transparent proxies, such as BSDs\'
				pf or Linux’s IPTables. If you’re planning to
				use Tor as a transparent proxy for a network, you’ll
				want to examine and change VirtualAddrNetwork from the
				default setting. You’ll also want to set the
				TransListenAddress option for the network you’d like
				to proxy. (Default: 0)</p>',
		'TransListenAddress' => '<p class="tor_option_description"><b>TransListenAddress</b>
				<i>IP</i>[:<i>PORT</i>]</p><p class="tor_option_description_indented">Bind to this address to listen
				for transparent proxy connections. (Default: 127.0.0.1).
				This is useful for exporting a transparent proxy server to
				an entire network. (DEPRECATED: As of 0.2.3.x−alpha,
				you can now use multiple TransPort entries, and provide
				addresses for TransPort entries, so TransListenAddress no
				longer has a purpose. For backward compatibility,
				TransListenAddress is only allowed when TransPort is just a
				port number.)</p>',
		'TransProxyType' => '<p class="tor_option_description"><b>TransProxyType
				default</b>|<b>TPROXY</b>|<b>ipfw</b>|<b>pf−divert</b></p><p class="tor_option_description_indented">TransProxyType may only be
				enabled when there is transparent proxy listener
				enabled.</p><p class="tor_option_description_indented">Set this to
				"TPROXY" if you wish to be able to use the TPROXY
				Linux module to transparently proxy connections that are
				configured using the TransPort option. This setting lets the
				listener on the TransPort accept connections for all
				addresses, even when the TransListenAddress is configured
				for an internal address. Detailed information on how to
				configure the TPROXY feature can be found in the Linux
				kernel source tree in the file
				Documentation/networking/tproxy.txt.</p><p class="tor_option_description_indented">Set this option
				to "ipfw" to use the FreeBSD ipfw interface.</p><p class="tor_option_description_indented">On *BSD
				operating systems when using pf, set this to
				"pf−divert" to take advantage of
				divert−to rules, which do not modify the packets like
				rdr−to rules do. Detailed information on how to
				configure pf to use divert−to rules can be found in
				the pf.conf(5) manual page. On OpenBSD, divert−to is
				available to use on versions greater than or equal to
				OpenBSD 4.4.</p><p class="tor_option_description_indented">Set this to
				"default", or leave it unconfigured, to use
				regular IPTables on Linux, or to use pf rdr−to rules
				on *BSD systems.</p><p class="tor_option_description_indented">(Default:
				"default".)</p>',
		'NATDPort' => '<p class="tor_option_description"><b>NATDPort</b>
				[<i>address</i>:]<i>port</i>|<b>auto</b> [<i>isolation
				flags</i>]</p><p class="tor_option_description_indented">Open this port to listen for
				connections from old versions of ipfw (as included in old
				versions of FreeBSD, etc) using the NATD protocol. Use 0 if
				you don’t want to allow NATD connections. Set the port
				to "auto" to have Tor pick a port for you. This
				directive can be specified multiple times to bind to
				multiple addresses/ports. See SOCKSPort for an explanation
				of isolation flags.</p><p class="tor_option_description_indented">This option is
				only for people who cannot use TransPort. (Default: 0)</p>',
		'NATDListenAddress' => '<p class="tor_option_description"><b>NATDListenAddress</b>
				<i>IP</i>[:<i>PORT</i>]</p><p class="tor_option_description_indented">Bind to this address to listen
				for NATD connections. (DEPRECATED: As of
				0.2.3.x−alpha, you can now use multiple NATDPort
				entries, and provide addresses for NATDPort entries, so
				NATDListenAddress no longer has a purpose. For backward
				compatibility, NATDListenAddress is only allowed when
				NATDPort is just a port number.)</p>',
		'AutomapHostsOnResolve' => '<p class="tor_option_description"><b>AutomapHostsOnResolve
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is enabled,
				and we get a request to resolve an address that ends with
				one of the suffixes in <b>AutomapHostsSuffixes</b>, we map
				an unused virtual address to that address, and return the
				new virtual address. This is handy for making
				".onion" addresses work with applications that
				resolve an address and then connect to it. (Default: 0)</p>',
		'AutomapHostsSuffixes' => '<p class="tor_option_description"><b>AutomapHostsSuffixes</b>
				<i>SUFFIX</i>,<i>SUFFIX</i>,<i>...</i></p><p class="tor_option_description_indented">A comma−separated list of
				suffixes to use with <b>AutomapHostsOnResolve</b>. The
				"." suffix is equivalent to "all
				addresses." (Default: .exit,.onion).</p>',
		'DNSPort' => '<p class="tor_option_description"><b>DNSPort</b>
				[<i>address</i>:]<i>port</i>|<b>auto</b> [<i>isolation
				flags</i>]</p><p class="tor_option_description_indented">If non−zero, open this
				port to listen for UDP DNS requests, and resolve them
				anonymously. This port only handles A, AAAA, and PTR
				requests−−−it doesn’t handle
				arbitrary DNS request types. Set the port to
				"auto" to have Tor pick a port for you. This
				directive can be specified multiple times to bind to
				multiple addresses/ports. See SOCKSPort for an explanation
				of isolation flags. (Default: 0)</p>',
		'DNSListenAddress' => '<p class="tor_option_description"><b>DNSListenAddress</b>
				<i>IP</i>[:<i>PORT</i>]</p><p class="tor_option_description_indented">Bind to this address to listen
				for DNS connections. (DEPRECATED: As of 0.2.3.x−alpha,
				you can now use multiple DNSPort entries, and provide
				addresses for DNSPort entries, so DNSListenAddress no longer
				has a purpose. For backward compatibility, DNSListenAddress
				is only allowed when DNSPort is just a port number.)</p>',
		'ClientDNSRejectInternalAddresses' => '<p class="tor_option_description"><b>ClientDNSRejectInternalAddresses
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If true, Tor does not believe
				any anonymously retrieved DNS answer that tells it that an
				address resolves to an internal address (like 127.0.0.1 or
				192.168.0.1). This option prevents certain
				browser−based attacks; don’t turn it off unless
				you know what you’re doing. (Default: 1)</p>',
		'ClientRejectInternalAddresses' => '<p class="tor_option_description"><b>ClientRejectInternalAddresses
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If true, Tor does not try to
				fulfill requests to connect to an internal address (like
				127.0.0.1 or 192.168.0.1) <i>unless a exit node is
				specifically requested</i> (for example, via a .exit
				hostname, or a controller request). (Default: 1)</p>',
		'DownloadExtraInfo' => '<p class="tor_option_description"><b>DownloadExtraInfo
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If true, Tor downloads and
				caches "extra−info" documents. These
				documents contain information about servers other than the
				information in their regular server descriptors. Tor does
				not use this information for anything itself; to save
				bandwidth, leave this option turned off. (Default: 0)</p>',
		'WarnPlaintextPorts' => '<p class="tor_option_description"><b>WarnPlaintextPorts</b>
				<i>port</i>,<i>port</i>,<i>...</i></p><p class="tor_option_description_indented">Tells Tor to issue a warnings
				whenever the user tries to make an anonymous connection to
				one of these ports. This option is designed to alert users
				to services that risk sending passwords in the clear.
				(Default: 23,109,110,143)</p>',
		'RejectPlaintextPorts' => '<p class="tor_option_description"><b>RejectPlaintextPorts</b>
				<i>port</i>,<i>port</i>,<i>...</i></p><p class="tor_option_description_indented">Like WarnPlaintextPorts, but
				instead of warning about risky port uses, Tor will instead
				refuse to make the connection. (Default: None)</p>',
		'AllowSingleHopCircuits' => '<p class="tor_option_description"><b>AllowSingleHopCircuits
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set, the
				attached Tor controller can use relays that have the
				<b>AllowSingleHopExits</b> option turned on to build
				one−hop Tor connections. (Default: 0)</p>',
		'OptimisticData' => '<p class="tor_option_description"><b>OptimisticData
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">When this option is set, and
				Tor is using an exit node that supports the feature, it will
				try optimistically to send data to the exit node without
				waiting for the exit node to report whether the connection
				succeeded. This can save a round−trip time for
				protocols like HTTP where the client talks first. If
				OptimisticData is set to <b>auto</b>, Tor will look at the
				UseOptimisticData parameter in the networkstatus. (Default:
				auto)</p>',
		'Tor2webMode' => '<p class="tor_option_description"><b>Tor2webMode
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set, Tor
				connects to hidden services <b>non−anonymously</b>.
				This option also disables client connections to
				non−hidden−service hostnames through Tor. It
				<b>must only</b> be used when running a tor2web Hidden
				Service web proxy. To enable this option the compile time
				flag −−enable−tor2webmode must be
				specified. (Default: 0)</p>',
		'Tor2webRendezvousPoints' => '<p class="tor_option_description"><b>Tor2webRendezvousPoints</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity
				fingerprints, nicknames, country codes and address patterns
				of nodes that are allowed to be used as RPs in HS circuits;
				any other nodes will not be used as RPs. (Example:
				Tor2webRendezvousPoints Fastyfasty,
				ABCD1234CDEF5678ABCD1234CDEF5678ABCD1234, {cc},
				255.254.0.0/8)</p><p class="tor_option_description_indented">This feature
				can only be used if Tor2webMode is also enabled.</p><p class="tor_option_description_indented">ExcludeNodes
				have higher priority than Tor2webRendezvousPoints, which
				means that nodes specified in ExcludeNodes will not be
				picked as RPs.</p><p class="tor_option_description_indented">If no nodes in
				Tor2webRendezvousPoints are currently available for use, Tor
				will choose a random node when building HS circuits.</p>',
		'UseMicrodescriptors' => '<p class="tor_option_description"><b>UseMicrodescriptors
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">Microdescriptors are a smaller
				version of the information that Tor needs in order to build
				its circuits. Using microdescriptors makes Tor clients
				download less directory information, thus saving bandwidth.
				Directory caches need to fetch regular descriptors and
				microdescriptors, so this option doesn’t save any
				bandwidth for them. If this option is set to
				"auto" (recommended) then it is on for all clients
				that do not set FetchUselessDescriptors. (Default: auto)</p>',
		'UseNTorHandshake' => '<p class="tor_option_description"><b>UseNTorHandshake
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">The "ntor"
				circuit−creation handshake is faster and (we think)
				more secure than the original ("TAP") circuit
				handshake, but starting to use it too early might make your
				client stand out. If this option is 0, your Tor client
				won’t use the ntor handshake. If it’s 1, your
				Tor client will use the ntor handshake to extend circuits
				through servers that support it. If this option is
				"auto", then your client will use the ntor
				handshake once enough directory authorities recommend it.
				(Default: 1)</p>',
		'PathBiasCircThreshold' => '<p class="tor_option_description"><b>PathBiasCircThreshold</b>
				<i>NUM</i></p>',
		'PathBiasNoticeRate' => '<p class="tor_option_description"><b>PathBiasNoticeRate</b>
				<i>NUM</i></p>',
		'PathBiasWarnRate' => '<p class="tor_option_description"><b>PathBiasWarnRate</b>
				<i>NUM</i></p>',
		'PathBiasExtremeRate' => '<p class="tor_option_description"><b>PathBiasExtremeRate</b>
				<i>NUM</i></p>',
		'PathBiasDropGuards' => '<p class="tor_option_description"><b>PathBiasDropGuards</b>
				<i>NUM</i></p>',
		'PathBiasScaleThreshold' => '<p class="tor_option_description"><b>PathBiasScaleThreshold</b>
				<i>NUM</i></p><p class="tor_option_description_indented">These options override the
				default behavior of Tor’s (<b>currently
				experimental</b>) path bias detection algorithm. To try to
				find broken or misbehaving guard nodes, Tor looks for nodes
				where more than a certain fraction of circuits through that
				guard fail to get built.</p><p class="tor_option_description_indented">The
				PathBiasCircThreshold option controls how many circuits we
				need to build through a guard before we make these checks.
				The PathBiasNoticeRate, PathBiasWarnRate and
				PathBiasExtremeRate options control what fraction of
				circuits must succeed through a guard so we won’t
				write log messages. If less than PathBiasExtremeRate
				circuits succeed <b>and</b> PathBiasDropGuards is set to 1,
				we disable use of that guard.</p><p class="tor_option_description_indented">When we have
				seen more than PathBiasScaleThreshold circuits through a
				guard, we scale our observations by 0.5 (governed by the
				consensus) so that new observations don’t get swamped
				by old ones.</p><p class="tor_option_description_indented">By default, or
				if a negative value is provided for one of these options,
				Tor uses reasonable defaults from the networkstatus
				consensus document. If no defaults are available there,
				these options default to 150, .70, .50, .30, 0, and 300
				respectively.</p>',
		'PathBiasUseThreshold' => '<p class="tor_option_description"><b>PathBiasUseThreshold</b>
				<i>NUM</i></p>',
		'PathBiasNoticeUseRate' => '<p class="tor_option_description"><b>PathBiasNoticeUseRate</b>
				<i>NUM</i></p>',
		'PathBiasExtremeUseRate' => '<p class="tor_option_description"><b>PathBiasExtremeUseRate</b>
				<i>NUM</i></p>',
		'PathBiasScaleUseThreshold' => '<p class="tor_option_description"><b>PathBiasScaleUseThreshold</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Similar to the above options,
				these options override the default behavior of Tor’s
				(<b>currently experimental</b>) path use bias detection
				algorithm.</p><p class="tor_option_description_indented">Where as the
				path bias parameters govern thresholds for successfully
				building circuits, these four path use bias parameters
				govern thresholds only for circuit usage. Circuits which
				receive no stream usage are not counted by this detection
				algorithm. A used circuit is considered successful if it is
				capable of carrying streams or otherwise receiving
				well−formed responses to RELAY cells.</p><p class="tor_option_description_indented">By default, or
				if a negative value is provided for one of these options,
				Tor uses reasonable defaults from the networkstatus
				consensus document. If no defaults are available there,
				these options default to 20, .80, .60, and 100,
				respectively.</p>',
		'ClientUseIPv6' => '<p class="tor_option_description"><b>ClientUseIPv6
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 1, Tor
				might connect to entry nodes over IPv6. Note that clients
				configured with an IPv6 address in a <b>Bridge</b> line will
				try connecting over IPv6 even if <b>ClientUseIPv6</b> is set
				to 0. (Default: 0)</p>',
		'ClientPreferIPv6ORPort' => '<p class="tor_option_description"><b>ClientPreferIPv6ORPort
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 1, Tor
				prefers an OR port with an IPv6 address over one with IPv4
				if a given entry node has both. Other things may influence
				the choice. This option breaks a tie to the favor of IPv6.
				(Default: 0)</p>',
		'PathsNeededToBuildCircuits' => '<p class="tor_option_description"><b>PathsNeededToBuildCircuits</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Tor clients don’t build
				circuits for user traffic until they know about enough of
				the network so that they could potentially construct enough
				of the possible paths through the network. If this option is
				set to a fraction between 0.25 and 0.95, Tor won’t
				build circuits until it has enough descriptors or
				microdescriptors to construct that fraction of possible
				paths. Note that setting this option too low can make your
				Tor client less anonymous, and setting it too high can
				prevent your Tor client from bootstrapping. If this option
				is negative, Tor will use a default value chosen by the
				directory authorities. (Default: −1.)</p>',
		'Address' => '<p class="tor_option_description"><b>Address</b>
				<i>address</i></p><p class="tor_option_description_indented">The IP address or fully
				qualified domain name of this server (e.g. moria.mit.edu).
				You can leave this unset, and Tor will guess your IP
				address. This IP address is the one used to tell clients and
				other servers where to find your Tor server; it
				doesn’t affect the IP that your Tor client binds to.
				To bind to a different address, use the *ListenAddress and
				OutboundBindAddress options.</p>',
		'AllowSingleHopExits' => '<p class="tor_option_description"><b>AllowSingleHopExits
				0</b>|<b>1</b></p><p class="tor_option_description_indented">This option controls whether
				clients can use this server as a single hop proxy. If set to
				1, clients can use this server as an exit even if it is the
				only hop in the circuit. Note that most clients will refuse
				to use servers that set this option, since most clients have
				ExcludeSingleHopRelays set. (Default: 0)</p>',
		'AssumeReachable' => '<p class="tor_option_description"><b>AssumeReachable
				0</b>|<b>1</b></p><p class="tor_option_description_indented">This option is used when
				bootstrapping a new Tor network. If set to 1, don’t do
				self−reachability testing; just upload your server
				descriptor immediately. If <b>AuthoritativeDirectory</b> is
				also set, this option instructs the dirserver to bypass
				remote reachability testing too and list all connected
				servers as running.</p>',
		'BridgeRelay' => '<p class="tor_option_description"><b>BridgeRelay
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Sets the relay to act as a
				"bridge" with respect to relaying connections from
				bridge users to the Tor network. It mainly causes Tor to
				publish a server descriptor to the bridge database, rather
				than to the public directory authorities.</p>',
		'ContactInfo' => '<p class="tor_option_description"><b>ContactInfo</b>
				<i>email_address</i></p><p class="tor_option_description_indented">Administrative contact
				information for this relay or bridge. This line can be used
				to contact you if your relay or bridge is misconfigured or
				something else goes wrong. Note that we archive and publish
				all descriptors containing these lines and that Google
				indexes them, so spammers might also collect them. You may
				want to obscure the fact that it’s an email address
				and/or generate a new address for this purpose.</p>',
		'ExitRelay' => '<p class="tor_option_description"><b>ExitRelay
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">Tells Tor whether to run as an
				exit relay. If Tor is running as a non−bridge server,
				and ExitRelay is set to 1, then Tor allows traffic to exit
				according to the ExitPolicy option (or the default
				ExitPolicy if none is specified).</p><p class="tor_option_description_indented">If ExitRelay is
				set to 0, no traffic is allowed to exit, and the ExitPolicy
				option is ignored.</p><p class="tor_option_description_indented">If ExitRelay is
				set to "auto", then Tor behaves as if it were set
				to 1, but warns the user if this would cause traffic to
				exit. In a future version, the default value will be 0.
				(Default: auto)</p>',
		'ExitPolicy' => '<p class="tor_option_description"><b>ExitPolicy</b>
				<i>policy</i>,<i>policy</i>,<i>...</i></p><p class="tor_option_description_indented">Set an exit policy for this
				server. Each policy is of the form
				"<b>accept[6]</b>|<b>reject[6]</b><i>ADDR</i>[/<i>MASK</i>][:<i>PORT</i>]".
				If /<i>MASK</i> is omitted then this policy just applies to
				the host given. Instead of giving a host or network you can
				also use "*" to denote the universe (0.0.0.0/0 and
				::/128), or *4 to denote all IPv4 addresses, and *6 to
				denote all IPv6 addresses. <i>PORT</i> can be a single port
				number, an interval of ports
				"<i>FROM_PORT</i>−<i>TO_PORT</i>", or
				"*". If <i>PORT</i> is omitted, that means
				"*".</p><p class="tor_option_description_indented">For example,
				"accept 18.7.22.69:*,reject 18.0.0.0/8:*,accept
				*:*" would reject any IPv4 traffic destined for MIT
				except for web.mit.edu, and accept any other IPv4 or IPv6
				traffic.</p><p class="tor_option_description_indented">Tor also allows
				IPv6 exit policy entries. For instance, "reject6
				[FC00::]/7:*" rejects all destinations that share 7
				most significant bit prefix with address FC00::.
				Respectively, "accept6 [C000::]/3:*" accepts all
				destinations that share 3 most significant bit prefix with
				address C000::.</p><p class="tor_option_description_indented">accept6 and
				reject6 only produce IPv6 exit policy entries. Using an IPv4
				address with accept6 or reject6 is ignored and generates a
				warning. accept/reject allows either IPv4 or IPv6 addresses.
				Use *4 as an IPv4 wildcard address, and *6 as an IPv6
				wildcard address. accept/reject * expands to matching IPv4
				and IPv6 wildcard address rules.</p><p class="tor_option_description_indented">To specify all
				IPv4 and IPv6 internal and link−local networks
				(including 0.0.0.0/8, 169.254.0.0/16, 127.0.0.0/8,
				192.168.0.0/16, 10.0.0.0/8, 172.16.0.0/12, [::]/8,
				[FC00::]/7, [FE80::]/10, [FEC0::]/10, [FF00::]/8, and
				[::]/127), you can use the "private" alias instead
				of an address. ("private" always produces rules
				for IPv4 and IPv6 addresses, even when used with
				accept6/reject6.)</p><p class="tor_option_description_indented">Private
				addresses are rejected by default (at the beginning of your
				exit policy), along with the configured primary public IPv4
				and IPv6 addresses, and any public IPv4 and IPv6 addresses
				on any interface on the relay. These private addresses are
				rejected unless you set the ExitPolicyRejectPrivate config
				option to 0. For example, once you’ve done that, you
				could allow HTTP to 127.0.0.1 and block all other
				connections to internal networks with "accept
				127.0.0.1:80,reject private:*", though that may also
				allow connections to your own computer that are addressed to
				its public (external) IP address. See RFC 1918 and RFC 3330
				for more details about internal and reserved IP address
				space.</p><p class="tor_option_description_indented">This directive
				can be specified multiple times so you don’t have to
				put it all on one line.</p><p class="tor_option_description_indented">Policies are
				considered first to last, and the first match wins. If you
				want to allow the same ports on IPv4 and IPv6, write your
				rules using accept/reject *. If you want to allow different
				ports on IPv4 and IPv6, write your IPv6 rules using
				accept6/reject6 *6, and your IPv4 rules using accept/reject
				*4. If you want to _replace_ the default exit policy, end
				your exit policy with either a reject *:* or an accept *:*.
				Otherwise, you’re _augmenting_ (prepending to) the
				default exit policy. The default exit policy is:</p><p class="tor_option_description_indented2">reject *:25
				<br>
				reject *:119 <br>
				reject *:135−139 <br>
				reject *:445 <br>
				reject *:563 <br>
				reject *:1214 <br>
				reject *:4661−4666 <br>
				reject *:6346−6429 <br>
				reject *:6699 <br>
				reject *:6881−6999 <br>
				accept *:*</p><p class="tor_option_description_indented2">Since the
				default exit policy uses accept/reject *, it applies to both
				<br>
				IPv4 and IPv6 addresses.</p>',
		'ExitPolicyRejectPrivate' => '<p class="tor_option_description"><b>ExitPolicyRejectPrivate
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Reject all private (local)
				networks, along with your own configured public IPv4 and
				IPv6 addresses, at the beginning of your exit policy. Also
				reject any public IPv4 and IPv6 addresses on any interface
				on the relay. (If IPv6Exit is not set, all IPv6 addresses
				will be rejected anyway.) See above entry on ExitPolicy.
				(Default: 1)</p>',
		'IPv6Exit' => '<p class="tor_option_description"><b>IPv6Exit
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set, and we are an exit
				node, allow clients to use us for IPv6 traffic. (Default:
				0)</p>',
		'MaxOnionQueueDelay' => '<p class="tor_option_description"><b>MaxOnionQueueDelay</b>
				<i>NUM</i> [<b>msec</b>|<b>second</b>]</p><p class="tor_option_description_indented">If we have more onionskins
				queued for processing than we can process in this amount of
				time, reject new ones. (Default: 1750 msec)</p>',
		'MyFamily' => '<p class="tor_option_description"><b>MyFamily</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">Declare that this Tor server is
				controlled or administered by a group or organization
				identical or similar to that of the other servers, defined
				by their identity fingerprints. When two servers both
				declare that they are in the same \'family\', Tor clients will
				not use them in the same circuit. (Each server only needs to
				list the other servers in its family; it doesn’t need
				to list itself, but it won’t hurt.) Do not list any
				bridge relay as it would compromise its concealment.</p><p class="tor_option_description_indented">When listing a
				node, it’s better to list it by fingerprint than by
				nickname: fingerprints are more reliable.</p>',
		'Nickname' => '<p class="tor_option_description"><b>Nickname</b>
				<i>name</i></p><p class="tor_option_description_indented">Set the server’s nickname
				to \'name\'. Nicknames must be between 1 and 19 characters
				inclusive, and must contain only the characters
				[a−zA−Z0−9].</p>',
		'NumCPUs' => '<p class="tor_option_description"><b>NumCPUs</b>
				<i>num</i></p><p class="tor_option_description_indented">How many processes to use at
				once for decrypting onionskins and other parallelizable
				operations. If this is set to 0, Tor will try to detect how
				many CPUs you have, defaulting to 1 if it can’t tell.
				(Default: 0)</p>',
		'ORPort' => '<p class="tor_option_description"><b>ORPort</b>
				[<i>address</i>:]<i>PORT</i>|<b>auto</b> [<i>flags</i>]</p><p class="tor_option_description_indented">Advertise this port to listen
				for connections from Tor clients and servers. This option is
				required to be a Tor server. Set it to "auto" to
				have Tor pick a port for you. Set it to 0 to not run an
				ORPort at all. This option can occur more than once.
				(Default: 0)</p><p class="tor_option_description_indented2">Tor recognizes
				these flags on each ORPort: <br>
				**NoAdvertise**:: <br>
				By default, we bind to a port and tell our users about it.
				If <br>
				NoAdvertise is specified, we don\'t advertise, but listen
				anyway. This <br>
				can be useful if the port everybody will be connecting to
				(for <br>
				example, one that\'s opened on our firewall) is somewhere
				else. <br>
				**NoListen**:: <br>
				By default, we bind to a port and tell our users about it.
				If <br>
				NoListen is specified, we don\'t bind, but advertise anyway.
				This <br>
				can be useful if something else (for example, a firewall\'s
				port <br>
				forwarding configuration) is causing connections to reach
				us. <br>
				**IPv4Only**:: <br>
				If the address is absent, or resolves to both an IPv4 and an
				IPv6 <br>
				address, only listen to the IPv4 address. <br>
				**IPv6Only**:: <br>
				If the address is absent, or resolves to both an IPv4 and an
				IPv6 <br>
				address, only listen to the IPv6 address.</p><p class="tor_option_description_indented2">For obvious
				reasons, NoAdvertise and NoListen are mutually exclusive,
				and <br>
				IPv4Only and IPv6Only are mutually exclusive.</p>',
		'ORListenAddress' => '<p class="tor_option_description"><b>ORListenAddress</b>
				<i>IP</i>[:<i>PORT</i>]</p><p class="tor_option_description_indented">Bind to this IP address to
				listen for connections from Tor clients and servers. If you
				specify a port, bind to this port rather than the one
				specified in ORPort. (Default: 0.0.0.0) This directive can
				be specified multiple times to bind to multiple
				addresses/ports.</p><p class="tor_option_description_indented2">This option is
				deprecated; you can get the same behavior with ORPort now
				<br>
				that it supports NoAdvertise and explicit addresses.</p>',
		'PortForwarding' => '<p class="tor_option_description"><b>PortForwarding
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Attempt to automatically
				forward the DirPort and ORPort on a NAT router connecting
				this Tor server to the Internet. If set, Tor will try both
				NAT−PMP (common on Apple routers) and UPnP (common on
				routers from other manufacturers). (Default: 0)</p>',
		'PortForwardingHelper' => '<p class="tor_option_description"><b>PortForwardingHelper</b>
				<i>filename</i>|<i>pathname</i></p><p class="tor_option_description_indented">If PortForwarding is set, use
				this executable to configure the forwarding. If set to a
				filename, the system path will be searched for the
				executable. If set to a path, only the specified path will
				be executed. (Default: tor−fw−helper)</p>',
		'PublishServerDescriptor' => '<p class="tor_option_description"><b>PublishServerDescriptor
				0</b>|<b>1</b>|<b>v3</b>|<b>bridge</b>,<b>...</b></p><p class="tor_option_description_indented">This option specifies which
				descriptors Tor will publish when acting as a relay. You can
				choose multiple arguments, separated by commas.</p><p class="tor_option_description_indented">If this option
				is set to 0, Tor will not publish its descriptors to any
				directories. (This is useful if you’re testing out
				your server, or if you’re using a Tor controller that
				handles directory publishing for you.) Otherwise, Tor will
				publish its descriptors of all type(s) specified. The
				default is "1", which means "if running as a
				server, publish the appropriate descriptors to the
				authorities".</p>',
		'ShutdownWaitLength' => '<p class="tor_option_description"><b>ShutdownWaitLength</b>
				<i>NUM</i></p><p class="tor_option_description_indented">When we get a SIGINT and
				we’re a server, we begin shutting down: we close
				listeners and start refusing new circuits. After <b>NUM</b>
				seconds, we exit. If we get a second SIGINT, we exit
				immediately. (Default: 30 seconds)</p>',
		'SSLKeyLifetime' => '<p class="tor_option_description"><b>SSLKeyLifetime</b>
				<i>N</i>
				<b>minutes</b>|<b>hours</b>|<b>days</b>|<b>weeks</b></p><p class="tor_option_description_indented">When creating a link
				certificate for our outermost SSL handshake, set its
				lifetime to this amount of time. If set to 0, Tor will
				choose some reasonable random defaults. (Default: 0)</p>',
		'HeartbeatPeriod' => '<p class="tor_option_description"><b>HeartbeatPeriod</b>
				<i>N</i>
				<b>minutes</b>|<b>hours</b>|<b>days</b>|<b>weeks</b></p><p class="tor_option_description_indented">Log a heartbeat message every
				<b>HeartbeatPeriod</b> seconds. This is a log level
				<i>notice</i> message, designed to let you know your Tor
				server is still alive and doing useful things. Settings this
				to 0 will disable the heartbeat. (Default: 6 hours)</p>',
		'AccountingMax' => '<p class="tor_option_description"><b>AccountingMax</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b>|<b>TBytes</b></p><p class="tor_option_description_indented">Limits the max number of bytes
				sent and received within a set time period using a given
				calculation rule (see: AccountingStart, AccountingRule).
				Useful if you need to stay under a specific bandwidth. By
				default, the number used for calculation is the max of
				either the bytes sent or received. For example, with
				AccountingMax set to 1 GByte, a server could send 900 MBytes
				and receive 800 MBytes and continue running. It will only
				hibernate once one of the two reaches 1 GByte. This can be
				changed to use the sum of the both bytes received and sent
				by setting the AccountingRule option to "sum"
				(total bandwidth in/out). When the number of bytes remaining
				gets low, Tor will stop accepting new connections and
				circuits. When the number of bytes is exhausted, Tor will
				hibernate until some time in the next accounting period. To
				prevent all servers from waking at the same time, Tor will
				also wait until a random point in each period before waking
				up. If you have bandwidth cost issues, enabling hibernation
				is preferable to setting a low bandwidth, since it provides
				users with a collection of fast servers that are up some of
				the time, which is more useful than a set of slow servers
				that are always "available".</p>',
		'AccountingRule' => '<p class="tor_option_description"><b>AccountingRule
				sum</b>|<b>max</b></p><p class="tor_option_description_indented">How we determine when our
				AccountingMax has been reached (when we should hibernate)
				during a time interval. Set to "max" to calculate
				using the higher of either the sent or received bytes (this
				is the default functionality). Set to "sum" to
				calculate using the sent plus received bytes. (Default:
				max)</p>',
		'AccountingStart' => '<p class="tor_option_description"><b>AccountingStart
				day</b>|<b>week</b>|<b>month</b> [<i>day</i>]
				<i>HH:MM</i></p><p class="tor_option_description_indented">Specify how long accounting
				periods last. If <b>month</b> is given, each accounting
				period runs from the time <i>HH:MM</i> on the <i>dayth</i>
				day of one month to the same day and time of the next. (The
				day must be between 1 and 28.) If <b>week</b> is given, each
				accounting period runs from the time <i>HH:MM</i> of the
				<i>dayth</i> day of one week to the same day and time of the
				next week, with Monday as day 1 and Sunday as day 7. If
				<b>day</b> is given, each accounting period runs from the
				time <i>HH:MM</i> each day to the same time on the next day.
				All times are local, and given in 24−hour time.
				(Default: "month 1 0:00")</p>',
		'RefuseUnknownExits' => '<p class="tor_option_description"><b>RefuseUnknownExits
				0</b>|<b>1</b>|<b>auto</b></p><p class="tor_option_description_indented">Prevent nodes that don’t
				appear in the consensus from exiting using this relay. If
				the option is 1, we always block exit attempts from such
				nodes; if it’s 0, we never do, and if the option is
				"auto", then we do whatever the authorities
				suggest in the consensus (and block if the consensus is
				quiet on the issue). (Default: auto)</p>',
		'ServerDNSResolvConfFile' => '<p class="tor_option_description"><b>ServerDNSResolvConfFile</b>
				<i>filename</i></p><p class="tor_option_description_indented">Overrides the default DNS
				configuration with the configuration in <i>filename</i>. The
				file format is the same as the standard Unix
				"<b>resolv.conf</b>" file (7). This option, like
				all other ServerDNS options, only affects name lookups that
				your server does on behalf of clients. (Defaults to use the
				system DNS configuration.)</p>',
		'ServerDNSAllowBrokenConfig' => '<p class="tor_option_description"><b>ServerDNSAllowBrokenConfig
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is false, Tor
				exits immediately if there are problems parsing the system
				DNS configuration or connecting to nameservers. Otherwise,
				Tor continues to periodically retry the system nameservers
				until it eventually succeeds. (Default: 1)</p>',
		'ServerDNSSearchDomains' => '<p class="tor_option_description"><b>ServerDNSSearchDomains
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, then we will
				search for addresses in the local search domain. For
				example, if this system is configured to believe it is in
				"example.com", and a client tries to connect to
				"www", the client will be connected to
				"www.example.com". This option only affects name
				lookups that your server does on behalf of clients.
				(Default: 0)</p>',
		'ServerDNSDetectHijacking' => '<p class="tor_option_description"><b>ServerDNSDetectHijacking
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set to 1,
				we will test periodically to determine whether our local
				nameservers have been configured to hijack failing DNS
				requests (usually to an advertising site). If they are, we
				will attempt to correct this. This option only affects name
				lookups that your server does on behalf of clients.
				(Default: 1)</p>',
		'ServerDNSTestAddresses' => '<p class="tor_option_description"><b>ServerDNSTestAddresses</b>
				<i>address</i>,<i>address</i>,<i>...</i></p><p class="tor_option_description_indented">When we’re detecting DNS
				hijacking, make sure that these <i>valid</i> addresses
				aren’t getting redirected. If they are, then our DNS
				is completely useless, and we’ll reset our exit policy
				to "reject <b>:</b>". This option only affects
				name lookups that your server does on behalf of clients.
				(Default: "www.google.com, www.mit.edu, www.yahoo.com,
				www.slashdot.org")</p>',
		'ServerDNSAllowNonRFC953Hostnames' => '<p class="tor_option_description"><b>ServerDNSAllowNonRFC953Hostnames
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is disabled,
				Tor does not try to resolve hostnames containing illegal
				characters (like @ and :) rather than sending them to an
				exit node to be resolved. This helps trap accidental
				attempts to resolve URLs and so on. This option only affects
				name lookups that your server does on behalf of clients.
				(Default: 0)</p>',
		'BridgeRecordUsageByCountry' => '<p class="tor_option_description"><b>BridgeRecordUsageByCountry
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is enabled and
				BridgeRelay is also enabled, and we have GeoIP data, Tor
				keeps a per−country count of how many client addresses
				have contacted it so that it can help the bridge authority
				guess which countries have blocked access to it. (Default:
				1)</p>',
		'ServerDNSRandomizeCase' => '<p class="tor_option_description"><b>ServerDNSRandomizeCase
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set, Tor
				sets the case of each character randomly in outgoing DNS
				requests, and makes sure that the case matches in DNS
				replies. This so−called "0x20 hack" helps
				resist some types of DNS poisoning attack. For more
				information, see "Increased DNS Forgery Resistance
				through 0x20−Bit Encoding". This option only
				affects name lookups that your server does on behalf of
				clients. (Default: 1)</p>',
		'GeoIPFile' => '<p class="tor_option_description"><b>GeoIPFile</b>
				<i>filename</i></p><p class="tor_option_description_indented">A filename containing IPv4
				GeoIP data, for use with by−country statistics.</p>',
		'GeoIPv6File' => '<p class="tor_option_description"><b>GeoIPv6File</b>
				<i>filename</i></p><p class="tor_option_description_indented">A filename containing IPv6
				GeoIP data, for use with by−country statistics.</p>',
		'TLSECGroup' => '<p class="tor_option_description"><b>TLSECGroup
				P224</b>|<b>P256</b></p><p class="tor_option_description_indented">What EC group should we try to
				use for incoming TLS connections? P224 is faster, but makes
				us stand out more. Has no effect if we’re a client, or
				if our OpenSSL version lacks support for ECDHE. (Default:
				P256)</p>',
		'CellStatistics' => '<p class="tor_option_description"><b>CellStatistics
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Relays only. When this option
				is enabled, Tor collects statistics about cell processing
				(i.e. mean time a cell is spending in a queue, mean number
				of cells in a queue and mean number of processed cells per
				circuit) and writes them into disk every 24 hours. Onion
				router operators may use the statistics for performance
				monitoring. If ExtraInfoStatistics is enabled, it will
				published as part of extra−info document. (Default:
				0)</p>',
		'DirReqStatistics' => '<p class="tor_option_description"><b>DirReqStatistics
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Relays and bridges only. When
				this option is enabled, a Tor directory writes statistics on
				the number and response time of network status requests to
				disk every 24 hours. Enables relay and bridge operators to
				monitor how much their server is being used by clients to
				learn about Tor network. If ExtraInfoStatistics is enabled,
				it will published as part of extra−info document.
				(Default: 1)</p>',
		'EntryStatistics' => '<p class="tor_option_description"><b>EntryStatistics
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Relays only. When this option
				is enabled, Tor writes statistics on the number of directly
				connecting clients to disk every 24 hours. Enables relay
				operators to monitor how much inbound traffic that
				originates from Tor clients passes through their server to
				go further down the Tor network. If ExtraInfoStatistics is
				enabled, it will be published as part of extra−info
				document. (Default: 0)</p>',
		'ExitPortStatistics' => '<p class="tor_option_description"><b>ExitPortStatistics
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Exit relays only. When this
				option is enabled, Tor writes statistics on the number of
				relayed bytes and opened stream per exit port to disk every
				24 hours. Enables exit relay operators to measure and
				monitor amounts of traffic that leaves Tor network through
				their exit node. If ExtraInfoStatistics is enabled, it will
				be published as part of extra−info document. (Default:
				0)</p>',
		'ConnDirectionStatistics' => '<p class="tor_option_description"><b>ConnDirectionStatistics
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Relays only. When this option
				is enabled, Tor writes statistics on the amounts of traffic
				it passes between itself and other relays to disk every 24
				hours. Enables relay operators to monitor how much their
				relay is being used as middle node in the circuit. If
				ExtraInfoStatistics is enabled, it will be published as part
				of extra−info document. (Default: 0)</p>',
		'HiddenServiceStatistics' => '<p class="tor_option_description"><b>HiddenServiceStatistics
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Relays only. When this option
				is enabled, a Tor relay writes obfuscated statistics on its
				role as hidden−service directory, introduction point,
				or rendezvous point to disk every 24 hours. If
				ExtraInfoStatistics is also enabled, these statistics are
				further published to the directory authorities. (Default:
				1)</p>',
		'ExtraInfoStatistics' => '<p class="tor_option_description"><b>ExtraInfoStatistics
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is enabled,
				Tor includes previously gathered statistics in its
				extra−info documents that it uploads to the directory
				authorities. (Default: 1)</p>',
		'ExtendAllowPrivateAddresses' => '<p class="tor_option_description"><b>ExtendAllowPrivateAddresses
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is enabled,
				Tor routers allow EXTEND request to localhost, RFC1918
				addresses, and so on. This can create security issues; you
				should probably leave it off. (Default: 0)</p>',
		'MaxMemInQueues' => '<p class="tor_option_description"><b>MaxMemInQueues</b>
				<i>N</i> <b>bytes</b>|<b>KB</b>|<b>MB</b>|<b>GB</b></p><p class="tor_option_description_indented">This option configures a
				threshold above which Tor will assume that it needs to stop
				queueing or buffering data because it’s about to run
				out of memory. If it hits this threshold, it will begin
				killing circuits until it has recovered at least 10% of this
				memory. Do not set this option too low, or your relay may be
				unreliable under load. This option only affects some queues,
				so the actual process size will be larger than this. If this
				option is set to 0, Tor will try to pick a reasonable
				default based on your system’s physical memory.
				(Default: 0)</p>',
		'SigningKeyLifetime' => '<p class="tor_option_description"><b>SigningKeyLifetime</b>
				<i>N</i> <b>days</b>|<b>weeks</b>|<b>months</b></p><p class="tor_option_description_indented">For how long should each
				Ed25519 signing key be valid? Tor uses a permanent master
				identity key that can be kept offline, and periodically
				generates new "signing" keys that it uses online.
				This option configures their lifetime. (Default: 30
				days)</p>',
		'OfflineMasterKey' => '<p class="tor_option_description"><b>OfflineMasterKey
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If non−zero, the Tor
				relay will never generate or load its master secret key.
				Instead, you’ll have to use "tor
				−−keygen" to manage the master secret key.
				(Default: 0)</p>',
		'DirPortFrontPage' => '<p class="tor_option_description"><b>DirPortFrontPage</b>
				<i>FILENAME</i></p><p class="tor_option_description_indented">When this option is set, it
				takes an HTML file and publishes it as "/" on the
				DirPort. Now relay operators can provide a disclaimer
				without needing to set up a separate webserver.
				There’s a sample disclaimer in
				contrib/operator−tools/tor−exit−notice.html.</p>',
		'HidServDirectoryV2' => '<p class="tor_option_description"><b>HidServDirectoryV2
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set, Tor
				accepts and serves v2 hidden service descriptors. Setting
				DirPort is not required for this, because clients connect
				via the ORPort by default. (Default: 1)</p>',
		'DirPort' => '<p class="tor_option_description"><b>DirPort</b>
				[<i>address</i>:]<i>PORT</i>|<b>auto</b> [<i>flags</i>]</p><p class="tor_option_description_indented">If this option is nonzero,
				advertise the directory service on this port. Set it to
				"auto" to have Tor pick a port for you. This
				option can occur more than once, but only one advertised
				DirPort is supported: all but one DirPort must have the
				<b>NoAdvertise</b> flag set. (Default: 0)</p><p class="tor_option_description_indented2">The same flags
				are supported here as are supported by ORPort.</p>',
		'DirListenAddress' => '<p class="tor_option_description"><b>DirListenAddress</b>
				<i>IP</i>[:<i>PORT</i>]</p><p class="tor_option_description_indented">Bind the directory service to
				this address. If you specify a port, bind to this port
				rather than the one specified in DirPort. (Default: 0.0.0.0)
				This directive can be specified multiple times to bind to
				multiple addresses/ports.</p><p class="tor_option_description_indented2">This option is
				deprecated; you can get the same behavior with DirPort now
				<br>
				that it supports NoAdvertise and explicit addresses.</p>',
		'DirPolicy' => '<p class="tor_option_description"><b>DirPolicy</b>
				<i>policy</i>,<i>policy</i>,<i>...</i></p><p class="tor_option_description_indented">Set an entrance policy for this
				server, to limit who can connect to the directory ports. The
				policies have the same form as exit policies above, except
				that port specifiers are ignored. Any address not matched by
				some entry in the policy is accepted.</p>',
		'AuthoritativeDirectory' => '<p class="tor_option_description"><b>AuthoritativeDirectory
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set to 1,
				Tor operates as an authoritative directory server. Instead
				of caching the directory, it generates its own list of good
				servers, signs it, and sends that to the clients. Unless the
				clients already have you listed as a trusted directory, you
				probably do not want to set this option. Please coordinate
				with the other admins at tor−ops@torproject.org if you
				think you should be a directory.</p>',
		'V3AuthoritativeDirectory' => '<p class="tor_option_description"><b>V3AuthoritativeDirectory
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set in
				addition to <b>AuthoritativeDirectory</b>, Tor generates
				version 3 network statuses and serves descriptors, etc as
				described in doc/spec/dir−spec.txt (for Tor clients
				and servers running at least 0.2.0.x).</p>',
		'VersioningAuthoritativeDirectory' => '<p class="tor_option_description"><b>VersioningAuthoritativeDirectory
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set to 1,
				Tor adds information on which versions of Tor are still
				believed safe for use to the published directory. Each
				version 1 authority is automatically a versioning authority;
				version 2 authorities provide this service optionally. See
				<b>RecommendedVersions</b>,
				<b>RecommendedClientVersions</b>, and
				<b>RecommendedServerVersions</b>.</p>',
		'RecommendedVersions' => '<p class="tor_option_description"><b>RecommendedVersions</b>
				<i>STRING</i></p><p class="tor_option_description_indented">STRING is a
				comma−separated list of Tor versions currently
				believed to be safe. The list is included in each directory,
				and nodes which pull down the directory learn whether they
				need to upgrade. This option can appear multiple times: the
				values from multiple lines are spliced together. When this
				is set then <b>VersioningAuthoritativeDirectory</b> should
				be set too.</p>',
		'RecommendedPackageVersions' => '<p class="tor_option_description"><b>RecommendedPackageVersions</b>
				<i>PACKAGENAME VERSION URL
				DIGESTTYPE</i><b>=</b><i>DIGEST</i></p><p class="tor_option_description_indented">Adds "package" line
				to the directory authority’s vote. This information is
				used to vote on the correct URL and digest for the released
				versions of different Tor−related packages, so that
				the consensus can certify them. This line may appear any
				number of times.</p>',
		'RecommendedClientVersions' => '<p class="tor_option_description"><b>RecommendedClientVersions</b>
				<i>STRING</i></p><p class="tor_option_description_indented">STRING is a
				comma−separated list of Tor versions currently
				believed to be safe for clients to use. This information is
				included in version 2 directories. If this is not set then
				the value of <b>RecommendedVersions</b> is used. When this
				is set then <b>VersioningAuthoritativeDirectory</b> should
				be set too.</p>',
		'BridgeAuthoritativeDir' => '<p class="tor_option_description"><b>BridgeAuthoritativeDir
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set in
				addition to <b>AuthoritativeDirectory</b>, Tor accepts and
				serves server descriptors, but it caches and serves the main
				networkstatus documents rather than generating its own.
				(Default: 0)</p>',
		'MinUptimeHidServDirectoryV2' => '<p class="tor_option_description"><b>MinUptimeHidServDirectoryV2</b>
				<i>N</i>
				<b>seconds</b>|<b>minutes</b>|<b>hours</b>|<b>days</b>|<b>weeks</b></p><p class="tor_option_description_indented">Minimum uptime of a v2 hidden
				service directory to be accepted as such by authoritative
				directories. (Default: 25 hours)</p>',
		'RecommendedServerVersions' => '<p class="tor_option_description"><b>RecommendedServerVersions</b>
				<i>STRING</i></p><p class="tor_option_description_indented">STRING is a
				comma−separated list of Tor versions currently
				believed to be safe for servers to use. This information is
				included in version 2 directories. If this is not set then
				the value of <b>RecommendedVersions</b> is used. When this
				is set then <b>VersioningAuthoritativeDirectory</b> should
				be set too.</p>',
		'ConsensusParams' => '<p class="tor_option_description"><b>ConsensusParams</b>
				<i>STRING</i></p><p class="tor_option_description_indented">STRING is a
				space−separated list of key=value pairs that Tor will
				include in the "params" line of its networkstatus
				vote.</p>',
		'DirAllowPrivateAddresses' => '<p class="tor_option_description"><b>DirAllowPrivateAddresses
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor will accept
				server descriptors with arbitrary "Address"
				elements. Otherwise, if the address is not an IP address or
				is a private IP address, it will reject the server
				descriptor. (Default: 0)</p>',
		'AuthDirBadExit' => '<p class="tor_option_description"><b>AuthDirBadExit</b>
				<i>AddressPattern...</i></p><p class="tor_option_description_indented">Authoritative directories only.
				A set of address patterns for servers that will be listed as
				bad exits in any network status document this authority
				publishes, if <b>AuthDirListBadExits</b> is set.</p><p class="tor_option_description_indented">(The address
				pattern syntax here and in the options below is the same as
				for exit policies, except that you don’t need to say
				"accept" or "reject", and ports are not
				needed.)</p>',
		'AuthDirInvalid' => '<p class="tor_option_description"><b>AuthDirInvalid</b>
				<i>AddressPattern...</i></p><p class="tor_option_description_indented">Authoritative directories only.
				A set of address patterns for servers that will never be
				listed as "valid" in any network status document
				that this authority publishes.</p>',
		'AuthDirReject' => '<p class="tor_option_description"><b>AuthDirReject</b>
				<i>AddressPattern</i>...</p><p class="tor_option_description_indented">Authoritative directories only.
				A set of address patterns for servers that will never be
				listed at all in any network status document that this
				authority publishes, or accepted as an OR address in any
				descriptor submitted for publication by this authority.</p>',
		'AuthDirBadExitCCs' => '<p class="tor_option_description"><b>AuthDirBadExitCCs</b>
				<i>CC</i>,...</p>',
		'AuthDirInvalidCCs' => '<p class="tor_option_description"><b>AuthDirInvalidCCs</b>
				<i>CC</i>,...</p>',
		'AuthDirRejectCCs' => '<p class="tor_option_description"><b>AuthDirRejectCCs</b>
				<i>CC</i>,...</p><p class="tor_option_description_indented">Authoritative directories only.
				These options contain a comma−separated list of
				country codes such that any server in one of those country
				codes will be marked as a bad exit/invalid for use, or
				rejected entirely.</p>',
		'AuthDirListBadExits' => '<p class="tor_option_description"><b>AuthDirListBadExits
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Authoritative directories only.
				If set to 1, this directory has some opinion about which
				nodes are unsuitable as exit nodes. (Do not set this to 1
				unless you plan to list non−functioning exits as bad;
				otherwise, you are effectively voting in favor of every
				declared exit as an exit.)</p>',
		'AuthDirMaxServersPerAddr' => '<p class="tor_option_description"><b>AuthDirMaxServersPerAddr</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Authoritative directories only.
				The maximum number of servers that we will list as
				acceptable on a single IP address. Set this to "0"
				for "no limit". (Default: 2)</p>',
		'AuthDirMaxServersPerAuthAddr' => '<p class="tor_option_description"><b>AuthDirMaxServersPerAuthAddr</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Authoritative directories only.
				Like AuthDirMaxServersPerAddr, but applies to addresses
				shared with directory authorities. (Default: 5)</p>',
		'AuthDirFastGuarantee' => '<p class="tor_option_description"><b>AuthDirFastGuarantee</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">Authoritative directories only.
				If non−zero, always vote the Fast flag for any relay
				advertising this amount of capacity or more. (Default: 100
				KBytes)</p>',
		'AuthDirGuardBWGuarantee' => '<p class="tor_option_description"><b>AuthDirGuardBWGuarantee</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">Authoritative directories only.
				If non−zero, this advertised capacity or more is
				always sufficient to satisfy the bandwidth requirement for
				the Guard flag. (Default: 250 KBytes)</p>',
		'AuthDirPinKeys' => '<p class="tor_option_description"><b>AuthDirPinKeys
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Authoritative directories only.
				If non−zero, do not allow any relay to publish a
				descriptor if any other relay has reserved its
				&lt;Ed25519,RSA&gt; identity keypair. In all cases, Tor
				records every keypair it accepts in a journal if it is new,
				or if it differs from the most recently accepted pinning for
				one of the keys it contains. (Default: 0)</p>',
		'BridgePassword' => '<p class="tor_option_description"><b>BridgePassword</b>
				<i>Password</i></p><p class="tor_option_description_indented">If set, contains an HTTP
				authenticator that tells a bridge authority to serve all
				requested bridge information. Used by the (only partially
				implemented) "bridge community" design, where a
				community of bridge relay operators all use an alternate
				bridge directory authority, and their target user audience
				can periodically fetch the list of available community
				bridges to stay up−to−date. (Default: not
				set)</p>',
		'V3AuthVotingInterval' => '<p class="tor_option_description"><b>V3AuthVotingInterval</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">V3 authoritative directories
				only. Configures the server’s preferred voting
				interval. Note that voting will <i>actually</i> happen at an
				interval chosen by consensus from all the authorities\'
				preferred intervals. This time SHOULD divide evenly into a
				day. (Default: 1 hour)</p>',
		'V3AuthVoteDelay' => '<p class="tor_option_description"><b>V3AuthVoteDelay</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">V3 authoritative directories
				only. Configures the server’s preferred delay between
				publishing its vote and assuming it has all the votes from
				all the other authorities. Note that the actual time used is
				not the server’s preferred time, but the consensus of
				all preferences. (Default: 5 minutes)</p>',
		'V3AuthDistDelay' => '<p class="tor_option_description"><b>V3AuthDistDelay</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">V3 authoritative directories
				only. Configures the server’s preferred delay between
				publishing its consensus and signature and assuming it has
				all the signatures from all the other authorities. Note that
				the actual time used is not the server’s preferred
				time, but the consensus of all preferences. (Default: 5
				minutes)</p>',
		'V3AuthNIntervalsValid' => '<p class="tor_option_description"><b>V3AuthNIntervalsValid</b>
				<i>NUM</i></p><p class="tor_option_description_indented">V3 authoritative directories
				only. Configures the number of VotingIntervals for which
				each consensus should be valid for. Choosing high numbers
				increases network partitioning risks; choosing low numbers
				increases directory traffic. Note that the actual number of
				intervals used is not the server’s preferred number,
				but the consensus of all preferences. Must be at least 2.
				(Default: 3)</p>',
		'V3BandwidthsFile' => '<p class="tor_option_description"><b>V3BandwidthsFile</b>
				<i>FILENAME</i></p><p class="tor_option_description_indented">V3 authoritative directories
				only. Configures the location of the
				bandwidth−authority generated file storing information
				on relays\' measured bandwidth capacities. (Default:
				unset)</p>',
		'V3AuthUseLegacyKey' => '<p class="tor_option_description"><b>V3AuthUseLegacyKey
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set, the directory authority
				will sign consensuses not only with its own signing key, but
				also with a "legacy" key and certificate with a
				different identity. This feature is used to migrate
				directory authority keys in the event of a compromise.
				(Default: 0)</p>',
		'RephistTrackTime' => '<p class="tor_option_description"><b>RephistTrackTime</b>
				<i>N</i>
				<b>seconds</b>|<b>minutes</b>|<b>hours</b>|<b>days</b>|<b>weeks</b></p><p class="tor_option_description_indented">Tells an authority, or other
				node tracking node reliability and history, that
				fine−grained information about nodes can be discarded
				when it hasn’t changed for a given amount of time.
				(Default: 24 hours)</p>',
		'VoteOnHidServDirectoriesV2' => '<p class="tor_option_description"><b>VoteOnHidServDirectoriesV2
				0</b>|<b>1</b></p><p class="tor_option_description_indented">When this option is set in
				addition to <b>AuthoritativeDirectory</b>, Tor votes on
				whether to accept relays as hidden service directories.
				(Default: 1)</p>',
		'AuthDirHasIPv6Connectivity' => '<p class="tor_option_description"><b>AuthDirHasIPv6Connectivity
				0</b>|<b>1</b></p><p class="tor_option_description_indented">Authoritative directories only.
				When set to 0, OR ports with an IPv6 address are being
				accepted without reachability testing. When set to 1, IPv6
				OR ports are being tested just like IPv4 OR ports. (Default:
				0)</p>',
		'MinMeasuredBWsForAuthToIgnoreAdvertised' => '<p class="tor_option_description"><b>MinMeasuredBWsForAuthToIgnoreAdvertised</b>
				<i>N</i></p><p class="tor_option_description_indented">A total value, in abstract
				bandwidth units, describing how much measured total
				bandwidth an authority should have observed on the network
				before it will treat advertised bandwidths as wholly
				unreliable. (Default: 500)</p>',
		'HiddenServiceDir' => '<p class="tor_option_description"><b>HiddenServiceDir</b>
				<i>DIRECTORY</i></p><p class="tor_option_description_indented">Store data files for a hidden
				service in DIRECTORY. Every hidden service must have a
				separate directory. You may use this option multiple times
				to specify multiple services. DIRECTORY must be an existing
				directory. (Note: in current versions of Tor, if DIRECTORY
				is a relative path, it will be relative to current working
				directory of Tor instance, not to its DataDirectory. Do not
				rely on this behavior; it is not guaranteed to remain the
				same in future versions.)</p>',
		'HiddenServicePort' => '<p class="tor_option_description"><b>HiddenServicePort</b>
				<i>VIRTPORT</i> [<i>TARGET</i>]</p><p class="tor_option_description_indented">Configure a virtual port
				VIRTPORT for a hidden service. You may use this option
				multiple times; each time applies to the service using the
				most recent HiddenServiceDir. By default, this option maps
				the virtual port to the same port on 127.0.0.1 over TCP. You
				may override the target port, address, or both by specifying
				a target of addr, port, addr:port, or
				<b>unix:</b><i>path</i>. (You can specify an IPv6 target as
				[addr]:port.) You may also have multiple lines with the same
				VIRTPORT: when a user connects to that VIRTPORT, one of the
				TARGETs from those lines will be chosen at random.</p>',
		'PublishHidServDescriptors' => '<p class="tor_option_description"><b>PublishHidServDescriptors
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 0, Tor will run any
				hidden services you configure, but it won’t advertise
				them to the rendezvous directory. This option is only useful
				if you’re using a Tor controller that handles hidserv
				publishing for you. (Default: 1)</p>',
		'HiddenServiceVersion' => '<p class="tor_option_description"><b>HiddenServiceVersion</b>
				<i>version</i>,<i>version</i>,<i>...</i></p><p class="tor_option_description_indented">A list of rendezvous service
				descriptor versions to publish for the hidden service.
				Currently, only version 2 is supported. (Default: 2)</p>',
		'HiddenServiceAuthorizeClient' => '<p class="tor_option_description"><b>HiddenServiceAuthorizeClient</b>
				<i>auth−type
				client−name</i>,<i>client−name</i>,<i>...</i></p><p class="tor_option_description_indented">If configured, the hidden
				service is accessible for authorized clients only. The
				auth−type can either be \'basic\' for a
				general−purpose authorization protocol or \'stealth\'
				for a less scalable protocol that also hides service
				activity from unauthorized clients. Only clients that are
				listed here are authorized to access the hidden service.
				Valid client names are 1 to 16 characters long and only use
				characters in A−Za−z0−9+−_ (no
				spaces). If this option is set, the hidden service is not
				accessible for clients without authorization any more.
				Generated authorization data can be found in the hostname
				file. Clients need to put this authorization data in their
				configuration file using <b>HidServAuth</b>.</p>',
		'HiddenServiceAllowUnknownPorts' => '<p class="tor_option_description"><b>HiddenServiceAllowUnknownPorts
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, then connections
				to unrecognized ports do not cause the current hidden
				service to close rendezvous circuits. (Setting this to 0 is
				not an authorization mechanism; it is instead meant to be a
				mild inconvenience to port−scanners.) (Default: 0)</p>',
		'HiddenServiceMaxStreams' => '<p class="tor_option_description"><b>HiddenServiceMaxStreams</b>
				<i>N</i></p><p class="tor_option_description_indented">The maximum number of
				simultaneous streams (connections) per rendezvous circuit.
				(Setting this to 0 will allow an unlimited number of
				simultanous streams.) (Default: 0)</p>',
		'HiddenServiceMaxStreamsCloseCircuit' => '<p class="tor_option_description"><b>HiddenServiceMaxStreamsCloseCircuit
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, then exceeding
				<b>HiddenServiceMaxStreams</b> will cause the offending
				rendezvous circuit to be torn down, as opposed to stream
				creation requests that exceed the limit being silently
				ignored. (Default: 0)</p>',
		'RendPostPeriod' => '<p class="tor_option_description"><b>RendPostPeriod</b>
				<i>N</i>
				<b>seconds</b>|<b>minutes</b>|<b>hours</b>|<b>days</b>|<b>weeks</b></p><p class="tor_option_description_indented">Every time the specified period
				elapses, Tor uploads any rendezvous service descriptors to
				the directory servers. This information is also uploaded
				whenever it changes. (Default: 1 hour)</p>',
		'HiddenServiceDirGroupReadable' => '<p class="tor_option_description"><b>HiddenServiceDirGroupReadable
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set to 1,
				allow the filesystem group to read the hidden service
				directory and hostname file. If the option is set to 0, only
				owner is able to read the hidden service directory.
				(Default: 0) Has no effect on Windows.</p>',
		'HiddenServiceNumIntroductionPoints' => '<p class="tor_option_description"><b>HiddenServiceNumIntroductionPoints</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Number of introduction points
				the hidden service will have. You can’t have more than
				10. (Default: 3)</p>',
		'TestingTorNetwork' => '<p class="tor_option_description"><b>TestingTorNetwork
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If set to 1, Tor adjusts
				default values of the configuration options below, so that
				it is easier to set up a testing Tor network. May only be
				set if non−default set of DirAuthorities is set.
				Cannot be unset while Tor is running. (Default: 0)</p><p class="tor_option_description_indented2">ServerDNSAllowBrokenConfig
				1 <br>
				DirAllowPrivateAddresses 1 <br>
				EnforceDistinctSubnets 0 <br>
				AssumeReachable 1 <br>
				AuthDirMaxServersPerAddr 0 <br>
				AuthDirMaxServersPerAuthAddr 0 <br>
				ClientDNSRejectInternalAddresses 0 <br>
				ClientRejectInternalAddresses 0 <br>
				CountPrivateBandwidth 1 <br>
				ExitPolicyRejectPrivate 0 <br>
				ExtendAllowPrivateAddresses 1 <br>
				V3AuthVotingInterval 5 minutes <br>
				V3AuthVoteDelay 20 seconds <br>
				V3AuthDistDelay 20 seconds <br>
				MinUptimeHidServDirectoryV2 0 seconds <br>
				TestingV3AuthInitialVotingInterval 5 minutes <br>
				TestingV3AuthInitialVoteDelay 20 seconds <br>
				TestingV3AuthInitialDistDelay 20 seconds <br>
				TestingAuthDirTimeToLearnReachability 0 minutes <br>
				TestingEstimatedDescriptorPropagationTime 0 minutes <br>
				TestingServerDownloadSchedule 0, 0, 0, 5, 10, 15, 20, 30, 60
				<br>
				TestingClientDownloadSchedule 0, 0, 5, 10, 15, 20, 30, 60
				<br>
				TestingServerConsensusDownloadSchedule 0, 0, 5, 10, 15, 20,
				30, 60 <br>
				TestingClientConsensusDownloadSchedule 0, 0, 5, 10, 15, 20,
				30, 60 <br>
				TestingBridgeDownloadSchedule 60, 30, 30, 60 <br>
				TestingClientMaxIntervalWithoutRequest 5 seconds <br>
				TestingDirConnectionMaxStall 30 seconds <br>
				TestingConsensusMaxDownloadTries 80 <br>
				TestingDescriptorMaxDownloadTries 80 <br>
				TestingMicrodescMaxDownloadTries 80 <br>
				TestingCertMaxDownloadTries 80 <br>
				TestingEnableConnBwEvent 1 <br>
				TestingEnableCellStatsEvent 1 <br>
				TestingEnableTbEmptyEvent 1</p>',
		'TestingV3AuthInitialVotingInterval' => '<p class="tor_option_description"><b>TestingV3AuthInitialVotingInterval</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">Like V3AuthVotingInterval, but
				for initial voting interval before the first consensus has
				been created. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 30 minutes)</p>',
		'TestingV3AuthInitialVoteDelay' => '<p class="tor_option_description"><b>TestingV3AuthInitialVoteDelay</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">Like V3AuthVoteDelay, but for
				initial voting interval before the first consensus has been
				created. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 5 minutes)</p>',
		'TestingV3AuthInitialDistDelay' => '<p class="tor_option_description"><b>TestingV3AuthInitialDistDelay</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">Like V3AuthDistDelay, but for
				initial voting interval before the first consensus has been
				created. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 5 minutes)</p>',
		'TestingV3AuthVotingStartOffset' => '<p class="tor_option_description"><b>TestingV3AuthVotingStartOffset</b>
				<i>N</i> <b>seconds</b>|<b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">Directory authorities offset
				voting start time by this much. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 0)</p>',
		'TestingAuthDirTimeToLearnReachability' => '<p class="tor_option_description"><b>TestingAuthDirTimeToLearnReachability</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">After starting as an authority,
				do not make claims about whether routers are Running until
				this much time has passed. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 30 minutes)</p>',
		'TestingEstimatedDescriptorPropagationTime' => '<p class="tor_option_description"><b>TestingEstimatedDescriptorPropagationTime</b>
				<i>N</i> <b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">Clients try downloading server
				descriptors from directory caches after this time. Changing
				this requires that <b>TestingTorNetwork</b> is set.
				(Default: 10 minutes)</p>',
		'TestingMinFastFlagThreshold' => '<p class="tor_option_description"><b>TestingMinFastFlagThreshold</b>
				<i>N</i>
				<b>bytes</b>|<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">Minimum value for the Fast
				flag. Overrides the ordinary minimum taken from the
				consensus when TestingTorNetwork is set. (Default: 0.)</p>',
		'TestingServerDownloadSchedule' => '<p class="tor_option_description"><b>TestingServerDownloadSchedule</b>
				<i>N</i>,<i>N</i>,<i>...</i></p><p class="tor_option_description_indented">Schedule for when servers
				should download things in general. Changing this requires
				that <b>TestingTorNetwork</b> is set. (Default: 0, 0, 0, 60,
				60, 120, 300, 900, 2147483647)</p>',
		'TestingClientDownloadSchedule' => '<p class="tor_option_description"><b>TestingClientDownloadSchedule</b>
				<i>N</i>,<i>N</i>,<i>...</i></p><p class="tor_option_description_indented">Schedule for when clients
				should download things in general. Changing this requires
				that <b>TestingTorNetwork</b> is set. (Default: 0, 0, 60,
				300, 600, 2147483647)</p>',
		'TestingServerConsensusDownloadSchedule' => '<p class="tor_option_description"><b>TestingServerConsensusDownloadSchedule</b>
				<i>N</i>,<i>N</i>,<i>...</i></p><p class="tor_option_description_indented">Schedule for when servers
				should download consensuses. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 0, 0, 60, 300,
				600, 1800, 1800, 1800, 1800, 1800, 3600, 7200)</p>',
		'TestingClientConsensusDownloadSchedule' => '<p class="tor_option_description"><b>TestingClientConsensusDownloadSchedule</b>
				<i>N</i>,<i>N</i>,<i>...</i></p><p class="tor_option_description_indented">Schedule for when clients
				should download consensuses. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 0, 0, 60, 300,
				600, 1800, 3600, 3600, 3600, 10800, 21600, 43200)</p>',
		'TestingBridgeDownloadSchedule' => '<p class="tor_option_description"><b>TestingBridgeDownloadSchedule</b>
				<i>N</i>,<i>N</i>,<i>...</i></p><p class="tor_option_description_indented">Schedule for when clients
				should download bridge descriptors. Changing this requires
				that <b>TestingTorNetwork</b> is set. (Default: 3600, 900,
				900, 3600)</p>',
		'TestingClientMaxIntervalWithoutRequest' => '<p class="tor_option_description"><b>TestingClientMaxIntervalWithoutRequest</b>
				<i>N</i> <b>seconds</b>|<b>minutes</b></p><p class="tor_option_description_indented">When directory clients have
				only a few descriptors to request, they batch them until
				they have more, or until this amount of time has passed.
				Changing this requires that <b>TestingTorNetwork</b> is set.
				(Default: 10 minutes)</p>',
		'TestingDirConnectionMaxStall' => '<p class="tor_option_description"><b>TestingDirConnectionMaxStall</b>
				<i>N</i> <b>seconds</b>|<b>minutes</b></p><p class="tor_option_description_indented">Let a directory connection
				stall this long before expiring it. Changing this requires
				that <b>TestingTorNetwork</b> is set. (Default: 5
				minutes)</p>',
		'TestingConsensusMaxDownloadTries' => '<p class="tor_option_description"><b>TestingConsensusMaxDownloadTries</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Try this often to download a
				consensus before giving up. Changing this requires that
				<b>TestingTorNetwork</b> is set. (Default: 8)</p>',
		'TestingDescriptorMaxDownloadTries' => '<p class="tor_option_description"><b>TestingDescriptorMaxDownloadTries</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Try this often to download a
				server descriptor before giving up. Changing this requires
				that <b>TestingTorNetwork</b> is set. (Default: 8)</p>',
		'TestingMicrodescMaxDownloadTries' => '<p class="tor_option_description"><b>TestingMicrodescMaxDownloadTries</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Try this often to download a
				microdesc descriptor before giving up. Changing this
				requires that <b>TestingTorNetwork</b> is set. (Default:
				8)</p>',
		'TestingCertMaxDownloadTries' => '<p class="tor_option_description"><b>TestingCertMaxDownloadTries</b>
				<i>NUM</i></p><p class="tor_option_description_indented">Try this often to download a v3
				authority certificate before giving up. Changing this
				requires that <b>TestingTorNetwork</b> is set. (Default:
				8)</p>',
		'TestingDirAuthVoteExit' => '<p class="tor_option_description"><b>TestingDirAuthVoteExit</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity
				fingerprints, country codes, and address patterns of nodes
				to vote Exit for regardless of their uptime, bandwidth, or
				exit policy. See the <b>ExcludeNodes</b> option for more
				information on how to specify nodes.</p><p class="tor_option_description_indented">In order for
				this option to have any effect, <b>TestingTorNetwork</b> has
				to be set. See the <b>ExcludeNodes</b> option for more
				information on how to specify nodes.</p>',
		'TestingDirAuthVoteExitIsStrict' => '<p class="tor_option_description"><b>TestingDirAuthVoteExitIsStrict
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If True (1), a node will never
				receive the Exit flag unless it is specified in the
				<b>TestingDirAuthVoteExit</b> list, regardless of its
				uptime, bandwidth, or exit policy.</p><p class="tor_option_description_indented">In order for
				this option to have any effect, <b>TestingTorNetwork</b> has
				to be set.</p>',
		'TestingDirAuthVoteGuard' => '<p class="tor_option_description"><b>TestingDirAuthVoteGuard</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity fingerprints
				and country codes and address patterns of nodes to vote
				Guard for regardless of their uptime and bandwidth. See the
				<b>ExcludeNodes</b> option for more information on how to
				specify nodes.</p><p class="tor_option_description_indented">In order for
				this option to have any effect, <b>TestingTorNetwork</b> has
				to be set.</p>',
		'TestingDirAuthVoteGuardIsStrict' => '<p class="tor_option_description"><b>TestingDirAuthVoteGuardIsStrict
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If True (1), a node will never
				receive the Guard flag unless it is specified in the
				<b>TestingDirAuthVoteGuard</b> list, regardless of its
				uptime and bandwidth.</p><p class="tor_option_description_indented">In order for
				this option to have any effect, <b>TestingTorNetwork</b> has
				to be set.</p>',
		'TestingDirAuthVoteHSDir' => '<p class="tor_option_description"><b>TestingDirAuthVoteHSDir</b>
				<i>node</i>,<i>node</i>,<i>...</i></p><p class="tor_option_description_indented">A list of identity fingerprints
				and country codes and address patterns of nodes to vote
				HSDir for regardless of their uptime and DirPort. See the
				<b>ExcludeNodes</b> option for more information on how to
				specify nodes.</p><p class="tor_option_description_indented">In order for
				this option to have any effect, <b>TestingTorNetwork</b> and
				<b>VoteOnHidServDirectoriesV2</b> both have to be set.</p>',
		'TestingDirAuthVoteHSDirIsStrict' => '<p class="tor_option_description"><b>TestingDirAuthVoteHSDirIsStrict
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If True (1), a node will never
				receive the HSDir flag unless it is specified in the
				<b>TestingDirAuthVoteHSDir</b> list, regardless of its
				uptime and DirPort.</p><p class="tor_option_description_indented">In order for
				this option to have any effect, <b>TestingTorNetwork</b> has
				to be set.</p>',
		'TestingEnableConnBwEvent' => '<p class="tor_option_description"><b>TestingEnableConnBwEvent
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set, then Tor
				controllers may register for CONN_BW events. Changing this
				requires that <b>TestingTorNetwork</b> is set. (Default:
				0)</p>',
		'TestingEnableCellStatsEvent' => '<p class="tor_option_description"><b>TestingEnableCellStatsEvent
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set, then Tor
				controllers may register for CELL_STATS events. Changing
				this requires that <b>TestingTorNetwork</b> is set.
				(Default: 0)</p>',
		'TestingEnableTbEmptyEvent' => '<p class="tor_option_description"><b>TestingEnableTbEmptyEvent
				0</b>|<b>1</b></p><p class="tor_option_description_indented">If this option is set, then Tor
				controllers may register for TB_EMPTY events. Changing this
				requires that <b>TestingTorNetwork</b> is set. (Default:
				0)</p>',
		'TestingMinExitFlagThreshold' => '<p class="tor_option_description"><b>TestingMinExitFlagThreshold</b>
				<i>N</i>
				<b>KBytes</b>|<b>MBytes</b>|<b>GBytes</b>|<b>KBits</b>|<b>MBits</b>|<b>GBits</b></p><p class="tor_option_description_indented">Sets a lower−bound for
				assigning an exit flag when running as an authority on a
				testing network. Overrides the usual default lower bound of
				4 KB. (Default: 0)</p>',
		'TestingLinkCertifetime' => '<p class="tor_option_description"><b>TestingLinkCertifetime</b>
				<i>N</i>
				<b>seconds</b>|<b>minutes</b>|<b>hours</b>|<b>days</b>|<b>weeks</b>|<b>months</b></p><p class="tor_option_description_indented">Overrides the default lifetime
				for the certificates used to authenticate our X509 link cert
				with our ed25519 signing key. (Default: 2 days)</p>',
		'TestingAuthKeyLifetime' => '<p class="tor_option_description"><b>TestingAuthKeyLifetime</b>
				<i>N</i>
				<b>seconds</b>|<b>minutes</b>|<b>hours</b>|<b>days</b>|<b>weeks</b>|<b>months</b></p><p class="tor_option_description_indented">Overrides the default lifetime
				for a signing Ed25519 TLS Link authentication key. (Default:
				2 days)</p>',
		'TestingLinkKeySlop' => '<p class="tor_option_description"><b>TestingLinkKeySlop</b>
				<i>N</i> <b>seconds</b>|<b>minutes</b>|<b>hours</b>,
				<b>TestingAuthKeySlop</b> <i>N</i>
				<b>seconds</b>|<b>minutes</b>|<b>hours</b>,
				<b>TestingSigningKeySlop</b> <i>N</i>
				<b>seconds</b>|<b>minutes</b>|<b>hours</b></p><p class="tor_option_description_indented">How early before the official
				expiration of a an Ed25519 signing key do we replace it and
				issue a new key? (Default: 3 hours for link and auth; 1 day
				for signing.)</p>' 
);

/*
 * The descriptions are from tor (1) man page.
 * The authors are Roger Dingledine [arma at mit.edu], Nick Mathewson [nickm at alum.mit.edu].
 */
$tor_options_categories = array (
		array (
				'BandwidthRate',
				'BandwidthBurst',
				'MaxAdvertisedBandwidth',
				'RelayBandwidthRate',
				'RelayBandwidthBurst',
				'PerConnBWRate',
				'PerConnBWBurst',
				'ClientTransportPlugin',
				'ServerTransportPlugin',
				'ServerTransportListenAddr',
				'ServerTransportOptions',
				'ExtORPort',
				'ExtORPortCookieAuthFile',
				'ExtORPortCookieAuthFileGroupReadable',
				'ConnLimit',
				'DisableNetwork',
				'ConstrainedSockets',
				'ConstrainedSockSize',
				'ControlPort',
				'ognized',
				'ControlListenAddress',
				'ControlSocket',
				'ControlSocketsGroupWritable',
				'HashedControlPassword',
				'CookieAuthentication',
				'CookieAuthFile',
				'CookieAuthFileGroupReadable',
				'ControlPortWriteToFile',
				'ControlPortFileGroupReadable',
				'DataDirectory',
				'FallbackDir',
				'DirAuthority',
				'DirAuthorityFallbackRate',
				'AlternateDirAuthority',
				'AlternateBridgeAuthority',
				'DisableAllSwap',
				'DisableDebuggerAttachment',
				'FetchDirInfoEarly',
				'FetchDirInfoExtraEarly',
				'FetchHidServDescriptors',
				'FetchServerDescriptors',
				'FetchUselessDescriptors',
				'HTTPProxy',
				'HTTPProxyAuthenticator',
				'HTTPSProxy',
				'HTTPSProxyAuthenticator',
				'Sandbox',
				'Socks4Proxy',
				'Socks5Proxy',
				'Socks5ProxyUsername',
				'Socks5ProxyPassword',
				'SocksSocketsGroupWritable',
				'KeepalivePeriod',
				'Log',
				'LogMessageDomains',
				'OutboundBindAddress',
				'PidFile',
				'ProtocolWarnings',
				'PredictedPortsRelevanceTime',
				'RunAsDaemon',
				'LogTimeGranularity',
				'TruncateLogFile',
				'SafeLogging',
				'User',
				'HardwareAccel',
				'AccelName',
				'AccelDir',
				'AvoidDiskWrites',
				'CircuitPriorityHalflife',
				'DisableIOCP',
				'UserspaceIOCPBuffers',
				'UseFilteringSSLBufferevents',
				'CountPrivateBandwidth' 
		),
		array (
				'AllowInvalidNodes',
				'ExcludeSingleHopRelays',
				'Bridge',
				'LearnCircuitBuildTimeout',
				'CircuitBuildTimeout',
				'CircuitIdleTimeout',
				'CircuitStreamTimeout',
				'ClientOnly',
				'ExcludeNodes',
				'ExcludeExitNodes',
				'GeoIPExcludeUnknown',
				'ExitNodes',
				'EntryNodes',
				'StrictNodes',
				'FascistFirewall',
				'FirewallPorts',
				'ReachableAddresses',
				'ReachableDirAddresses',
				'ReachableORAddresses',
				'HidServAuth',
				'CloseHSClientCircuitsImmediatelyOnTimeout',
				'CloseHSServiceRendCircuitsImmediatelyOnTimeout',
				'LongLivedPorts',
				'MapAddress',
				'NewCircuitPeriod',
				'MaxCircuitDirtiness',
				'MaxClientCircuitsPending',
				'NodeFamily',
				'EnforceDistinctSubnets',
				'SOCKSPort',
				'SOCKSListenAddress',
				'SocksPolicy',
				'SocksTimeout',
				'TokenBucketRefillInterval',
				'TrackHostExits',
				'TrackHostExitsExpire',
				'UpdateBridgesFromAuthority',
				'UseBridges',
				'UseEntryGuards',
				'UseEntryGuardsAsDirGuards',
				'GuardfractionFile',
				'UseGuardFraction',
				'NumEntryGuards',
				'NumDirectoryGuards',
				'GuardLifetime',
				'SafeSocks',
				'TestSocks',
				'WarnUnsafeSocks',
				'VirtualAddrNetworkIPv4',
				'VirtualAddrNetworkIPv6',
				'AllowNonRFC953Hostnames',
				'AllowDotExit',
				'FastFirstHopPK',
				'TransPort',
				'TransListenAddress',
				'TransProxyType',
				'NATDPort',
				'NATDListenAddress',
				'AutomapHostsOnResolve',
				'AutomapHostsSuffixes',
				'DNSPort',
				'DNSListenAddress',
				'ClientDNSRejectInternalAddresses',
				'ClientRejectInternalAddresses',
				'DownloadExtraInfo',
				'WarnPlaintextPorts',
				'RejectPlaintextPorts',
				'AllowSingleHopCircuits',
				'OptimisticData',
				'Tor2webMode',
				'Tor2webRendezvousPoints',
				'UseMicrodescriptors',
				'UseNTorHandshake',
				'PathBiasCircThreshold',
				'PathBiasNoticeRate',
				'PathBiasWarnRate',
				'PathBiasExtremeRate',
				'PathBiasDropGuards',
				'PathBiasScaleThreshold',
				'PathBiasUseThreshold',
				'PathBiasNoticeUseRate',
				'PathBiasExtremeUseRate',
				'PathBiasScaleUseThreshold',
				'ClientUseIPv6',
				'ClientPreferIPv6ORPort',
				'PathsNeededToBuildCircuits' 
		),
		array (
				'Address',
				'AllowSingleHopExits',
				'AssumeReachable',
				'BridgeRelay',
				'ContactInfo',
				'ExitRelay',
				'ExitPolicy',
				'ExitPolicyRejectPrivate',
				'IPv6Exit',
				'MaxOnionQueueDelay',
				'MyFamily',
				'Nickname',
				'NumCPUs',
				'ORPort',
				'ORListenAddress',
				'PortForwarding',
				'PortForwardingHelper',
				'PublishServerDescriptor',
				'ShutdownWaitLength',
				'SSLKeyLifetime',
				'HeartbeatPeriod',
				'AccountingMax',
				'AccountingRule',
				'AccountingStart',
				'RefuseUnknownExits',
				'ServerDNSResolvConfFile',
				'ServerDNSAllowBrokenConfig',
				'ServerDNSSearchDomains',
				'ServerDNSDetectHijacking',
				'ServerDNSTestAddresses',
				'ServerDNSAllowNonRFC953Hostnames',
				'BridgeRecordUsageByCountry',
				'ServerDNSRandomizeCase',
				'GeoIPFile',
				'GeoIPv6File',
				'TLSECGroup',
				'CellStatistics',
				'DirReqStatistics',
				'EntryStatistics',
				'ExitPortStatistics',
				'ConnDirectionStatistics',
				'HiddenServiceStatistics',
				'ExtraInfoStatistics',
				'ExtendAllowPrivateAddresses',
				'MaxMemInQueues',
				'SigningKeyLifetime',
				'OfflineMasterKey' 
		),
		array (
				'DirPortFrontPage',
				'HidServDirectoryV2',
				'DirPort',
				'DirListenAddress',
				'DirPolicy' 
		),
		array (
				'AuthoritativeDirectory',
				'V3AuthoritativeDirectory',
				'VersioningAuthoritativeDirectory',
				'RecommendedVersions',
				'RecommendedPackageVersions',
				'RecommendedClientVersions',
				'BridgeAuthoritativeDir',
				'MinUptimeHidServDirectoryV2',
				'RecommendedServerVersions',
				'ConsensusParams',
				'DirAllowPrivateAddresses',
				'AuthDirBadExit',
				'AuthDirInvalid',
				'AuthDirReject',
				'AuthDirBadExitCCs',
				'AuthDirInvalidCCs',
				'AuthDirRejectCCs',
				'AuthDirListBadExits',
				'AuthDirMaxServersPerAddr',
				'AuthDirMaxServersPerAuthAddr',
				'AuthDirFastGuarantee',
				'AuthDirGuardBWGuarantee',
				'AuthDirPinKeys',
				'BridgePassword',
				'V3AuthVotingInterval',
				'V3AuthVoteDelay',
				'V3AuthDistDelay',
				'V3AuthNIntervalsValid',
				'V3BandwidthsFile',
				'V3AuthUseLegacyKey',
				'RephistTrackTime',
				'VoteOnHidServDirectoriesV2',
				'AuthDirHasIPv6Connectivity',
				'MinMeasuredBWsForAuthToIgnoreAdvertised' 
		),
		array (
				'HiddenServiceDir',
				'HiddenServicePort',
				'PublishHidServDescriptors',
				'HiddenServiceVersion',
				'HiddenServiceAuthorizeClient',
				'HiddenServiceAllowUnknownPorts',
				'HiddenServiceMaxStreams',
				'HiddenServiceMaxStreamsCloseCircuit',
				'RendPostPeriod',
				'HiddenServiceDirGroupReadable',
				'HiddenServiceNumIntroductionPoints' 
		),
		array (
				'TestingTorNetwork',
				'TestingV3AuthInitialVotingInterval',
				'TestingV3AuthInitialVoteDelay',
				'TestingV3AuthInitialDistDelay',
				'TestingV3AuthVotingStartOffset',
				'TestingAuthDirTimeToLearnReachability',
				'TestingEstimatedDescriptorPropagationTime',
				'TestingMinFastFlagThreshold',
				'TestingServerDownloadSchedule',
				'TestingClientDownloadSchedule',
				'TestingServerConsensusDownloadSchedule',
				'TestingClientConsensusDownloadSchedule',
				'TestingBridgeDownloadSchedule',
				'TestingClientMaxIntervalWithoutRequest',
				'TestingDirConnectionMaxStall',
				'TestingConsensusMaxDownloadTries',
				'TestingDescriptorMaxDownloadTries',
				'TestingMicrodescMaxDownloadTries',
				'TestingCertMaxDownloadTries',
				'TestingDirAuthVoteExit',
				'TestingDirAuthVoteExitIsStrict',
				'TestingDirAuthVoteGuard',
				'TestingDirAuthVoteGuardIsStrict',
				'TestingDirAuthVoteHSDir',
				'TestingDirAuthVoteHSDirIsStrict',
				'TestingEnableConnBwEvent',
				'TestingEnableCellStatsEvent',
				'TestingEnableTbEmptyEvent',
				'TestingMinExitFlagThreshold',
				'TestingLinkCertifetime',
				'TestingAuthKeyLifetime',
				'TestingLinkKeySlop' 
		) 
);

$tor_circuit_status_name_to_col = array (
		'BUILD_FLAGS' => 2,
		'PURPOSE' => 3,
		'TIME_CREATED' => 4 
);

function get_response() {
	global $tc;
	$result = fread ( $tc, tc_max_result_length );
	if ($result [3] === ' ')
		return $result;
	else {
		$code_space = substr ( $result, 0, 3 ) . ' ';
		$pos_new_line = 0;
		while ( 1 ) {
			while ( ($tmp = strpos ( $result, "\r\n", $pos_new_line )) !== false ) {
				$pos_new_line = $tmp + 2;
				$pre = substr ( $result, $pos_new_line, 4 );
				if ($pre == $code_space) {
					// to make sure the next line is empty
					if (($tmp = strpos ( $result, "\r\n", $pos_new_line )) !== false) {
						$pos_new_line = $tmp + 2;
						if ($pos_new_line == strlen ( $result ))
							return $result;
					}
				}
			}
			$result .= fread ( $tc, tc_max_result_length );
		}
	}
}

function exec_command($command) {
	global $tc;
	fwrite ( $tc, "$command\r\n" );
	return get_response ();
}

function exec_command_lines($command) {
	return explode ( "\r\n", exec_command ( $command ) );
}

function close_tc() {
	global $tc;
	exec_command ( 'quit' );
}

function is_invalid_var($var, $min, $max) {
	return (! isset ( $var )) || (filter_var ( $var, FILTER_VALIDATE_INT ) === false) || $var < $min || $var > $max;
}

function update_config() {
	global $error_message, $require_login, $tc_connection_method, $tc_connection_hostname, $tc_connection_auth;
	$config_contents = '<?php $require_login=' . $require_login . ';$tc_connection_method=' . $tc_connection_method . ';$tc_connection_hostname=' . var_export ( $tc_connection_hostname, 1 ) . ';$tc_connection_auth=' . $tc_connection_auth;
	if ($tc_connection_auth) {
		global $tc_connection_password;
		$config_contents .= ';$tc_connection_password=' . var_export ( $tc_connection_password, 1 );
	}
	if ($tc_connection_method == tc_connection_method_network) {
		global $tc_connection_secure, $tc_connection_port;
		$config_contents .= ';$tc_connection_secure=' . $tc_connection_secure . ';$tc_connection_port=' . $tc_connection_port;
	}
	if ($require_login) {
		global $login_password_hash;
		$config_contents .= ';$login_password_hash=' . var_export ( $login_password_hash, 1 );
	}
	if (! file_put_contents ( config_file_path, $config_contents . ";\n" ))
		$error_message .= '<p>Error writing to config file</p>';
}

function compare_version($v) {
	global $tor_version;
	for($a = 0; $a < 3; $a ++) {
		if ($tor_version [$a] > $v [$a])
			return 1;
		if ($tor_version [$a] < $v [$a])
			return 0;
	}
	return 1;
}
	
/*
 * This function outputs the following based on $parse_dir_data.
 * nickname
 * identity
 * digest
 * publication
 * ip
 * ORPort
 * DIRPort
 * IPv6 addresses, each ending with ";"
 * flags
 * bandwidth
 * portlist
 * version
 * Seperators are "\t".
 */
function output_parse_dir() {
	global $parse_dir_data;
	$result = '';
	for($a = 0; $a < 12; $a ++)
		if (isset ( $parse_dir_data [$a] ))
			$result .= "{$parse_dir_data[$a]}\t";
		else
			$result .= "\t";
	return $result;
}

/*
 * This function parses v3 style router status entry into the following:
 * nickname
 * identity
 * digest
 * publication
 * ip
 * ORPort
 * DIRPort
 * IPv6 addresses, each ending with ";"
 * flags
 * bandwidth
 * portlist
 * version
 * Seperators are "\t".
 * It returns a string containing these lines when encountering the next router status entry.
 */
function parse_dir($line) {
	global $parse_dir_data;
	static $parse_dir_started = 0;
	$line_1 = substr ( $line, 2 );
	switch ($line [0]) {
		case 'r' :
			$a = explode ( ' ', $line_1 );
			for($b = 0; $b < 8; $b ++)
				if (! isset ( $a [$b] ))
					$a [$b] = '';
			if ($parse_dir_started) {
				$result = output_parse_dir ();
				$parse_dir_data = array (
						$a [0],
						$a [1],
						$a [2],
						"$a[3] $a[4]",
						$a [5],
						$a [6],
						$a [7] 
				);
				return $result;
			}
			$parse_dir_data = array (
					$a [0],
					$a [1],
					$a [2],
					"$a[3] $a[4]",
					$a [5],
					$a [6],
					$a [7] 
			);
			$parse_dir_started = 1;
			break;
		case 'a' :
			if (isset ( $parse_dir_data [7] ))
				$parse_dir_data [7] .= "$line_1;";
			else
				$parse_dir_data [7] = "$line_1;";
			break;
		case 's' :
			$parse_dir_data [8] = $line_1;
			break;
		case 'v' :
			$parse_dir_data [11] = $line_1;
			break;
		case 'w' :
			$a = substr ( $line_1, 10 );
			if ($b = strstr ( $a, ' ', 1 ))
				$parse_dir_data [9] = $b;
			else
				$parse_dir_data [9] = $a;
			break;
		case 'p' :
			$parse_dir_data [10] = $line_1;
			break;
	}
	return null;
}

/*
 * This function parses v1 style router status entry into the following:
 * nickname
 * identity
 * digest
 * publication
 * ip
 * ORPort
 * DIRPort
 * IPv6 addresses, each ending with ";"
 * flags
 * bandwidth
 * portlist
 * version
 * Seperators are "\t".
 * It returns a string containing these lines when encountering the next router status entry.
 */
function parse_dir_v1($line) {
	global $parse_dir_data;
	static $parse_dir_started = 0;
	$a = strpos ( $line, ' ' );
	$line_1 = substr ( $line, $a );
	switch (substr ( $line, 0, 1 )) {
		case 'router' :
			$a = explode ( ' ', $line_1 );
			for($b = 0; $b < 5; $b ++)
				if (! isset ( $a [$b] ))
					$a [$b] = '';
			if ($parse_dir_started) {
				$result = output_parse_dir ();
				$parse_dir_data = array (
						0 => $a [0],
						4 => $a [1],
						5 => $a [2],
						6 => $a [4] 
				);
				return $result;
			}
			$parse_dir_data = array (
					0 => $a [0],
					4 => $a [1],
					5 => $a [2],
					6 => $a [4] 
			);
			$parse_dir_started = 1;
			break;
		case 'bandwidth' :
			$a = substr ( $line_1, 10 );
			if ($b = strstr ( $a, ' ', 1 ))
				$parse_dir_data [9] = $b;
			else
				$parse_dir_data [9] = $a;
			break;
		case 'published' :
			$a = explode ( ' ', $line_1 );
			if (! isset ( $a [1] ))
				$a [1] = '';
			$parse_dir_data [3] = "$a[0] $a[1]";
			break;
		case 'signing-key' :
			$parse_dir_data [1] = $line_1;
			break;
	}
	return null;
}

/*
 * This function parses 1 line of response of "getinfo status/circuit-status".
 * It outputs 1 line of the following:
 * id
 * status
 * build flag
 * purpose
 * time created
 * path
 * HS state
 * HS address
 * reason
 * remote reason
 * socks username
 * socks password
 * Seperators are " ".
 */
function output_circuit_status($line) {
	$name_to_col = array (
			'BUILD_FLAGS' => 2,
			'PURPOSE' => 3,
			'HS_STATE' => 6,
			'REND_QUERY' => 7,
			'TIME_CREATED' => 4,
			'REASON' => 8,
			'REMOTE_REASON' => 9,
			'SOCKS_USERNAME' => 10,
			'SOCKS_PASSWORD' => 11 
	);
	$a = explode ( ' ', $line );
	if (! isset ( $a [1] ))
		$a [1] = '';
	$output_data = array (
			0 => $a [0],
			1 => $a [1] 
	);
	if (isset ( $a [2] )) {
		$output_data [5] = $a [2];
		for($b = 3; isset ( $a [$b] ); $b ++) {
			$c = $a [$b];
			$d = strpos ( $c, '=' );
			$e = substr ( $c, 0, $d );
			if (isset ( $name_to_col [$e] ))
				$output_data [$name_to_col [$e]] = substr ( $c, $d + 1 );
		}
	}
	for($b = 0; $b < 12; $b ++) {
		if (isset ( $output_data [$b] ))
			echo $output_data [$b], ' ';
		else
			echo ' ';
	}
	echo "\n";
}

if (isset ( $_POST ['action'] ))
	$action = $_POST ['action'];
elseif (isset ( $_GET ['action'] ))
	$action = $_GET ['action'];
else
	$action = '';

session_start ();

if (is_invalid_var ( $require_login, 0, 1 ))
	$require_login = 0;

check_login:
if ($require_login) {
	if (! isset ( $_SESSION ['php_tor_controller_loggedin'] ))
		$_SESSION ['php_tor_controller_loggedin'] = false;
	if ($action == 'login') {
		if (isset ( $_POST ['login_password'] ) && password_verify ( $_POST ['login_password'], $login_password_hash ))
			$_SESSION ['php_tor_controller_loggedin'] = true;
		else
			$error_message .= '<p>Login information wrong</p>';
	}
	if (! $_SESSION ['php_tor_controller_loggedin']) {
		if ($update_config)
			update_config ();
		?>
<!DOCTYPE html>
<html>
<head>
<title>PHP Tor Controller</title>
</head>
<body>
	<p>Logging in is required.</p>
	<form method="post">
		<input type="hidden" name="action" value="login">
		<table>
			<tbody>
				<tr>
					<td><label for="login_password"> password </label></td>
					<td><input id="login_password" name="login_password"
						type="password"></td>
				</tr>
			</tbody>
		</table>
		<input type="submit" value="Log in">
	</form>
</body>
</html>
<?php
		exit ();
	}
}

// actions to be resolved before connecting to tor control port
switch ($action) {
	case '' :
		break;
	case 'change_connection_method' :
		$action = '';
		if ($_POST ['connection_method'] == 'unix_socket') {
			$tc_connection_method = tc_connection_method_unix_socket;
			$tc_connection_hostname = $_POST ['connection_unix_socket_path'];
		} else {
			$tc_connection_method = tc_connection_method_network;
			$tc_connection_hostname = $_POST ['connection_tcp_hostname'];
			$tc_connection_port = $_POST ['connection_tcp_port'];
			switch ($_POST ['connection_tcp_secure']) {
				case 'ssl' :
					$tc_connection_secure = tc_connection_secure_ssl;
					break;
				case 'tls' :
					$tc_connection_secure = tc_connection_secure_tls;
					break;
				default :
					$tc_connection_secure = tc_connection_secure_none;
			}
		}
		switch ($_POST ['connection_auth_method']) {
			case 'password' :
				$tc_connection_auth = tc_connection_auth_password;
				$tc_connection_password = $_POST ['connection_auth_method_password_password'];
				break;
			case 'cookie' :
				$tc_connection_auth = tc_connection_auth_cookie;
				$tc_connection_password = $_POST ['connection_auth_method_cookie_path'];
				break;
			default :
				$tc_connection_auth = tc_connection_auth_none;
		}
		$update_config = 1;
		break;
	case 'change_login_method' :
		$action = '';
		if ($_POST ['login_method_require_login'] == 'login_method_require_login') {
			if (isset ( $_POST ['login_method_password'] )) {
				if (! $require_login) {
					$require_login = 1;
				} else
					$_SESSION ['php_tor_controller_loggedin'] = false;
				$login_password_hash = password_hash ( $_POST ['login_method_password'], PASSWORD_DEFAULT );
				$update_config = 1;
				goto check_login;
			} else
				$error_message .= '<p>Password is not entered.</p>';
		} else {
			$require_login = 0;
			$update_config = 1;
		}
		break;
}
if(isset($_SESSION['b']))
	$aaaa=0;
else{$_SESSION['b']=1;$aaaa=1;}
// to prevent other requests from being delayed executing
session_write_close ();

if (is_invalid_var ( $tc_connection_method, 1, 2 )) {
	$tc_connection_method = tc_connection_method_network;
	$update_config = 1;
}
if (! isset ( $tc_connection_hostname )) {
	if ($tc_connection_method == $tc_connection_method_unix_socket)
		$tc_connection_hostname = '/var/run/tor.sock';
	else
		$tc_connection_hostname = 'localhost';
	$update_config = 1;
}
if ($tc_connection_method == tc_connection_method_network) {
	if (is_invalid_var ( $tc_connection_port, 1, 65535 )) {
		$tc_connection_port = 9051;
		$update_config = 1;
	}
	if (is_invalid_var ( $tc_connection_secure, 0, 2 )) {
		$tc_connection_secure = tc_connection_secure_none;
		$update_config = 1;
	}
}
if (is_invalid_var ( $tc_connection_auth, 0, 2 )) {
	$tc_connection_auth = tc_connection_auth_none;
	$update_config = 1;
}
if ($tc_connection_auth && ! isset ( $tc_connection_password )) {
	$tc_connection_password = '';
	$update_config = 1;
}

if ($update_config)
	update_config ();

if ($tc_connection_method == tc_connection_method_network) {
	if (strstr ( $tc_connection_hostname, ':' ))
		$tc_connection_hostname = "[$tc_connection_hostname]";
	switch ($tc_connection_secure) {
		case tc_connection_secure_ssl :
			$tc = stream_socket_client ( "ssl://$tc_connection_hostname:$tc_connection_port", $errno, $errstr );
			break;
		case tc_connection_secure_tls :
			$tc = stream_socket_client ( "tls://$tc_connection_hostname:$tc_connection_port", $errno, $errstr );
			break;
		default :
			$tc = stream_socket_client ( "tcp://$tc_connection_hostname:$tc_connection_port", $errno, $errstr );
	}
} else {
	$tc = fsockopen ( "unix://$tc_connection_hostname", $errno, $errstr );
}

if ($tc) {
	$auth_success = 1;
	switch ($tc_connection_auth) {
		case tc_connection_auth_password :
			$command = 'authenticate "' . addslashes ( $tc_connection_password ) . '"';
			break;
		case tc_connection_auth_cookie :
			if (file_exists ( $tc_connection_password )) {
				if (($tc_connection_auth_cookie_file = fopen ( $tc_connection_password, "rb" )) === false) {
					$error_message .= '<p>Error opening cookie file</p>';
					$auth_success = 0;
				} else {
					if (($tc_connection_auth_cookie_contents = stream_get_contents ( $tc_connection_auth_cookie_file )) === false) {
						$error_message .= '<p>Error reading cookie file</p>';
						$auth_success = 0;
					} else
						$command = "authenticate " . bin2hex ( $tc_connection_auth_cookie_contents );
					fclose ( $tc_connection_auth_cookie_file );
				}
			} else {
				$error_message .= '<p>Cookie file does not exist or insufficient permission</p>';
				$auth_success = 0;
			}
			break;
		default :
			$command = 'authenticate';
	}
	if ($auth_success) {
		if (($response = exec_command ( $command )) == "250 OK\r\n") {
			// to get the current version
			$tor_version_string = strstr ( substr ( exec_command ( 'getinfo version' ), 12 ), "\r", 1 );
			$a = explode ( '.', $tor_version_string );
			for($b = 0; $b < 4; $b ++)
				$tor_version [$b] = ( int ) $a [$b];
				
			// actions to be resolved afted connecting to tor control port
			switch ($action) {
				case '' :
					break;
				case 'custom_command' :
					header ( 'Content-type: text/plain' );
					if (isset ( $_POST ['custom_command_command'] )) {
						echo exec_command ( $_POST ['custom_command_command'] );
					}
					close_tc ();
					exit ();
				case 'update_status' :
					/*
					 * data will be empty if something fails on the server side.
					 *
					 * The first 8 lines are the following status of tor:
					 * 	version
					 * 	network-liveness
					 * 	status/bootstrap-phase
					 * 	status/circuit-established
					 * 	status/enough-dir-info
					 * 	status/good-server-descriptor
					 * 	status/accepted-server-descriptor
					 * 	status/reachability-succeeded
					 * The next line is a number num in decimal meaning the
					 * lines for stream status.
					 * The next num lines are stream status, with " " at the end
					 * of each line.
					 * The next line is a number num in decimal meaning the
					 * lines for OR connection status.
					 * The next num lines are OR connection status, with " " at
					 * the end of each line.
					 * The next line is a number num in decimal meaning the
					 * lines for circuit status.
					 * The next num lines are circuit status. Each entry is
					 * 	id
					 * 	status
					 * 	build flag
					 * 	time created
					 * 	path
					 * Seperators are " ".
					 * The next line is a number num in decimal meaning the
					 * entries for OR status.
					 * The next num lines are OR status. Each entry is
					 * 	nickname
					 * 	identity
					 * 	digest
					 * 	publication
					 * 	ip
					 * 	ORPort
					 * 	DIRPort
					 * 	IPv6 addresses, each ending with ';'
					 * 	flags
					 * 	version
					 * 	bandwidth
					 * 	portlist
					 * Seperators are "\t".
					 * Next line is "a" followed by a timestamp in miliseconds
					 * in decimal meaning the next time_start or empty.
					 * Each of the next lines is a timestamp in miliseconds in
					 * decimal followed by a line of response for one of the
					 * following asynchronous events without "650" at the
					 * beginning of the line.
					 * 	bw
					 * 	info
					 * 	notice
					 * 	warn
					 * 	err
					 * Line breaks are "\n".
					 */
					
					header ( 'Content-type: text/plain' );
					
					echo $tor_version_string,"\n";
					
					$response_lines = exec_command_lines ( 'getinfo network-liveness status/bootstrap-phase status/circuit-established status/enough-dir-info status/good-server-descriptor status/accepted-server-descriptor status/reachability-succeeded stream-status orconn-status circuit-status' );
					for($index = 0; $index < 7; $index ++)
						echo substr ( strstr ( $response_lines [$index], '=' ), 1 ), "\n";
						
					// for stream-status
					$line = $response_lines [$index];
					if ($line [3] == '+') {
						$response_lines_1 = array ();
						$num = 0;
						for($index++;($line=$response_lines[$index])[0] != '.';$index++){
							$response_lines_1 [] = $line;
							$num ++;
						}
						$index ++;
						echo $num, "\n";
						foreach ( $response_lines_1 as $line )
							echo $line, " \n";
					} else {
						$line = substr ( $line, 18 );
						if (isset ( $line [0] )) {
							echo "1\n", $line, " \n";
						} else
							echo "0\n";
						$index ++;
					}
					
					// for orconn-status
					$line = $response_lines [$index];
					if ($line [3] == '+') {
						$response_lines_1 = array ();
						$num = 0;
						for($index++;($line=$response_lines[$index])[0] != '.';$index++){
							$response_lines_1 [] = $line;
							$num ++;
						}
						$index ++;
						echo $num, "\n";
						foreach ( $response_lines_1 as $line ) {
							$a = explode ( ' ', $line );
							echo $a [1], ' ', $a [0], " \n";
						}
					} else {
						$line = substr ( $line, 18 );
						if (isset ( $line [0] )) {
							$a = explode ( ' ', $line );
							echo "1\n", $a [1], ' ', $a [0], " \n";
						} else
							echo "0\n";
						$index ++;
					}
					
					// for circuit-status
					$line = $response_lines [$index];
					if ($line [3] == '+') {
						$response_lines_1 = array ();
						$num = 0;
						for($index++;($line=$response_lines[$index])[0] != '.';$index++){
							$response_lines_1 [] = $line;
							$num ++;
						}
						echo $num, "\n";
						foreach ( $response_lines_1 as $line ) {
							output_circuit_status ( $line );
						}
					} else {
						$line = substr ( $line, 19 );
						if (isset ( $line [0] )) {
							echo "1\n";
							output_circuit_status ( $line );
						} else
							echo "0\n";
					}
					
					// for ns/all
					$num = 0;
					$output_lines = array ();
					if (compare_version ( array (
							0,
							1,
							2,
							3 
					) )) // v3 directory style
					{
						$response_lines = exec_command_lines ( 'getinfo ns/all' );
						$line = $response_lines [0];
						if ($line [3] == '+') {
							unset ( $response_lines [0] );
							foreach ( $response_lines as $line ) {
								if ($line [0] == '.')
									break;
								if ($out = parse_dir ( $line )) {
									$output_lines [] = $out;
									$num ++;
								}
							}
							$output_lines [] = output_parse_dir ();
							$num ++;
						} else {
							$line = substr ( $line, 11 );
							if (isset ( $line [0] )) {
								parse_dir ( $line );
								$output_lines [] = output_parse_dir ();
								$num = 1;
							}
						}
					} else // v1 directory style
					{
						$response_lines = exec_command_lines ( 'getinfo network-status' );
						$line = $response_lines [0];
						if ($line [3] == '+') {
							unset ( $response_lines [0] );
							foreach ( $response_lines as $line ) {
								if ($line [0] == '.')
									break;
								if ($out = parse_dir_v1 ( $line )) {
									$output_lines [] = $out;
									$num ++;
								}
							}
							$output_lines [] = output_parse_dir ();
							$num ++;
						} else {
							$line = substr ( $line, 11 );
							if (isset ( $line [0] )) {
								parse_dir_v1 ( $line );
								$output_lines [] = output_parse_dir ();
								$num = 1;
							}
						}
					}
					echo $num, "\n";
					foreach ( $output_lines as $line ) {
						echo $line, "\n";
					}
						
					// for asynchronous events
					// $time_start is the time to start recording events in
					// miliseconds
					$now = ( int ) (microtime ( 1 ) * 1000);
					if (isset ( $_POST ['time_start'] ) &&
							(($time_start
									= filter_var ( $_POST ['time_start'], FILTER_VALIDATE_INT ))
									!== false) && ($time_start > $now) &&
							($time_start < $now + update_status_interval)) {
						
						echo "\n";
						if($aaaa)for($a=0;$a<65535;$a++)echo $now," INFO a\r\n";
						$response = exec_command ( 'setevents bw info notice warn err' );
						while ( ($now = ( int ) (microtime ( 1 ) * 1000)) < $time_start )
							$response = get_response ();

						// $time_stop is the time to stop recording events in
						// miliseconds
						$time_stop = $time_start + update_status_interval;
						while ( $now < $time_stop ) {
							foreach ( explode ( "\r\n", $response ) as $line )
								if (substr ( $line, 0, 3 ) == '650')
									echo $now, substr ( $line, 3 ), "\n";
							$response = get_response ();
							$now = ( int ) (microtime ( 1 ) * 1000);
						}
					} else {
							// If $time_start is not in the right range, it is reset.
						echo 'a', $now + update_status_interval * 1.25, "\n";
					}
					
					close_tc ();
					exit ();
				case 'geoip':
					/*
					 * $_POST['ip_addr'] should be IP addresses seperated by ";".
					 * The respnse for each IP address is a 2-letter country code.
					 * There are no seperators.
					 */
					header ( 'Content-type: text/plain' );
					$command = 'getinfo';
					$valid_addr_index = array ();
					$valid_addr_length = array ();
					$valid_addr_num = 0;
					$country = array ();
					if (isset ( $_POST ['ip_addr'] )) {
						$num = 0;
						foreach ( explode ( ";", $_POST ['ip_addr'] ) as $a ) {
							if ($b [0] == '[') {
								if ($b = filter_var ( strstr ( substr ( $b, 1 ), ']', 1 ), FILTER_VALIDATE_IP )) {
									$command .= " ip-to-country/$b";
									$valid_addr_index [] = $num;
									$valid_addr_length [] = strlen ( $b );
									$valid_addr_num ++;
								}
							} elseif ($b = filter_var ( $a, FILTER_VALIDATE_IP )) {
								$command .= " ip-to-country/$b";
								$valid_addr_index [] = $num;
								$valid_addr_length [] = strlen ( $b );
								$valid_addr_num ++;
							}
							$country [$num] = '??';
							$num ++;
						}
						if ($valid_addr_num) {
							$response_lines = exec_command_lines ( $command );
							$a = 0;
							foreach ( $response_lines as $line ) {
								$b = substr ( $line, $valid_addr_length [$a] + 19, 2 );
								if (strlen ( $b ) == 2)
									$country [$valid_addr_index [$a]] = $b;
								if (++ $a == $valid_addr_num)
									break;
							}
						}
						foreach ( $country as $a )
							echo $a;
					}
					close_tc ();
					exit ();
			}
			
			// to get all tor options
			$response_lines = exec_command_lines ( 'getinfo config/names' );
			unset ( $response_lines [0] ); // first line of response "250+config/names=\r\n" is skipped
			foreach ( $response_lines as $line ) {
				if ($line [0] == '.')
					break;
				$b = strpos ( $line, ' ' );
				$tor_options_name_length [] = $b;
				$name = substr ( $line, 0, $b );
				$tor_options_name [] = $name;
				$tor_options_name_reverse [$name] = $tor_options_number;
				$line = substr ( $line, $b + 1 );
				if ($c = strstr ( $line, ' ' ))
					$c = substr ( $c, 1 );
				else
					$c = $line;
				$tor_options_type [] = $c;
				$tor_options_number ++;
			}
			
			// to get values of the options
			$response_lines = exec_command_lines ( "getconf " . implode ( ' ', $tor_options_name ) );
			for($a = 0; $a < $tor_options_number; $a ++) {
				// each line is 250+<option name>, so we have $tor_options_name_length+4
				$b = substr ( $response_lines [$a], $tor_options_name_length [$a] + 4 );
				if ($b)
					$tor_options_value [$a] = substr ( $b, 1 );
					// false means default
				else
					$tor_options_value [$a] = false;
			}
			
			// to get default values of the options
			if (compare_version ( array (
					0,
					2,
					4,
					1 
			) )) {
				foreach ( exec_command_lines ( 'getinfo config/defaults' ) as $line ) {
					if ($line [0] == '.')
						break;
					$a = strpos ( $line, ' ' );
					$name = substr ( $line, 0, $a );
					$value = substr ( $line, $a + 1 );
					if (isset ( $tor_options_default_value [$name] ))
						$tor_options_default_value [$name] .= '<p class="tor_option_description_indented">' . htmlspecialchars ( $value ) . '</p>';
					else
						$tor_options_default_value [$name] = '<p class="tor_option_description"><b>Default value from tor:</b></p><p class="tor_option_description_indented">' . htmlspecialchars ( $value ) . '</p>';
				}
				
				foreach ( $tor_options_default_value as $name => $value ) {
					if (isset ( $tor_options_description [$name] ))
						$tor_options_description [$name] .= $value;
					else
						$tor_options_description [$name] = $value;
				}
			}
		} else {
			$error_message .= "<p>
						Authentication failed
					</p>
					<p>
						$response
					</p>
					<p>
						<a href=\"#connecting_to_tor\">go to settings for connecting to tor</a>
					</p>";
		}
	} else {
		exit ();
	}
} else {
	if ($action !== '')
		exit ();
	$error_message .= "<p>
				Failed to connect to tor.
			</p>
			<p>
				Error:
			</p>
			<p>
				$errno $errstr
			</p>
			<p>
				<a href=\"#connecting_to_tor\">go to settings for connecting to tor</a>
			</p>";
}

// to set the variables for the "Connecting to Tor" section
if ($tc_connection_method == tc_connection_method_network) {
	$connection_tcp_hostname = $tc_connection_hostname;
	$connection_tcp_port = $tc_connection_port;
	$connection_tcp_secure = $tc_connection_secure;
	$connection_unix_socket_path = '/var/run/tor.sock';
} else {
	$connection_tcp_hostname = 'localhost';
	$connection_tcp_port = 9051;
	$connection_tcp_secure = tc_connection_secure_none;
	$connection_unix_socket_path = $tc_connection_hostname;
}
switch ($tc_connection_auth) {
	case tc_connection_auth_none :
		$connection_auth_password = '';
		$connection_auth_cookie = '/etc/tor/control_auth_cookie';
		break;
	case tc_connection_auth_password :
		$connection_auth_password = $tc_connection_password;
		$connection_auth_cookie = '/etc/tor/control_auth_cookie';
		break;
	default :
		$connection_auth_password = '';
		$connection_auth_cookie = $tc_connection_password;
}

// all the output
?>
<!DOCTYPE html>
<html>
<head>
	<title>PHP Tor Controller</title>
	<link rel="stylesheet" type="text/css" href="src/style.css">
	<script src="src/jquery.min.js"></script>
	<script src="src/js_bintrees/treebase.js"></script>
	<script src="src/js_bintrees/rbtree.js"></script>
	<script src="src/script.js"></script>
	<script>
php_tor_controller_url = '<?=path_http?>';
tor_options_categories = [
<?php
foreach ( $tor_options_categories as $category ) {
	$in_category = array ();
	foreach ( $category as $name ) {
		if (isset ( $tor_options_name_reverse [$name] )) {
			$in_category [$tor_options_name_reverse [$name]] = 1;
		}
	}
	echo '[';
	$b = 0;
	$c = 0;
	for($a = 0; $a < $tor_options_number;) {
		if (isset ( $in_category [$a] ))
			$b |= 1 << $c;
		$c = (++ $a) & 31;
		if (! $c) {
			echo $b, ',';
			$b = 0;
		}
	}
	echo $b, '],';
}

// The last array means all options.
$b = $tor_options_number >> 5;
?>
[
<?php
for($a = 0; $a < $b; $a ++)
	echo '4294967295,';
?>
,4294967295]];
tor_options_number = <?=$tor_options_number?>;

//the names of the options
tor_options_name = [
<?php
$a = 0;
foreach ( $tor_options_name as $b ) {
	if ($a)
		echo ',';
	else
		$a = 1;
	echo '\'', $b, '\'';
}
?>
];

update_status_interval=<?=update_status_interval?>;

bandwidth_data_size=<?=bandwidth_data_size?>;

messages_data_size=<?=messages_data_size?>;
	</script>
</head>
<body onload="body_loaded();">
	<div id="command_" onclick="this.style.display='none';$('#command_response_box *').remove();">
		<table>
			<tbody>
				<tr id="command_prompt_top"></tr>
				<tr>
					<td class="command_prompt_col1"></td>
					<td class="command_prompt_col2">
						<div id="command_command_box"></div>
						<div id="command_response_box"></div>
					</td>
					<td class="command_prompt_col3"></td>
				</tr>
				<tr>
					<td class="command_prompt_col1"></td>
					<td class="command_prompt_col2">(click to dismiss)</td>
					<td class="command_prompt_col3"></td>
				</tr>
			</tbody>
		</table>
	</div>

	<div class="table_of_contents">
		<ul>
			<li>
				<a href="#status">status</a>
				<ul>
					<li>
						<a href="#streams_title">streams</a>
					</li>
					<li>
						<a href="#orconn_title">OR sonnections</a>
					</li>
					<li>
						<a href="#circuits_title">circuits</a>
					</li>
					<li>
						<a href="#or_title">ORs</a>
					</li>
					<li>
						<a href="#bandwidth_graph_title">bandwidth graph</a>
					</li>
					<li>
						<a href="#message_log_title">message log</a>
					</li>
				</ul>
			</li>
			<li>
				<a href="#new_identity">new identity</a>
			</li>
			<li>
				<a href="#custom_command">custom command</a>
			</li>
			<li>
				<a href="#tor_settings">Tor settings</a>
			</li>
			<li>
				<a href="#controller_settings">PHP Tor Controller settings</a>
				<ul>
					<li>
						<a href="#connecting_to_tor">connection to tor</a>
					</li>
					<li>
						<a href="#logging_in">logging in</a>
					</li>
				</ul>
			</li>
		</ul>
	</div>
	<div class="contents">

		<?=$error_message?>

		<h1 id="status">Status</h1>

		<table border="1" id="status_table">
			<tr>
			<tr>
				<th>connection to Tor</th>
				<td>failed</td>
			</tr>
				<th>Tor version</th>
				<td></td>
			</tr>
			<tr>
				<th>network reachability</th>
				<td></td>
			</tr>
			<tr>
				<th>bootstrap progress</th>
				<td></td>
			</tr>
			<tr>
				<th>circuit established</th>
				<td></td>
			</tr>
			<tr>
				<th>up-to-date dir info</th>
				<td></td>
			</tr>
			<tr>
				<th>good server descriptor</th>
				<td></td>
			</tr>
			<tr>
				<th>accepted server descriptor</th>
				<td></td>
			</tr>
			<tr>
				<th>reachability succeeded</th>
				<td></td>
			</tr>
		</table>

		<h2 id="streams_title">streams</h2>
		number of streams: <span id="stream_number">0</span>
		<div id="stream_list_box">
			<table border="1" class="wide_table">
				<thead id="streams_list_header">
					<tr>
						<th>id</th>
						<th>status</th>
						<th>circuit id</th>
						<th>target</th>
					</tr>
				</thead>
				<tbody id="streams_list"></tbody>
			</table>
		</div>

		<h2 id="orconn_title">OR connections</h2>
		number of OR connections: <span id="orconn_number">0</span>
		<div id="orconn_list_box">
			<table border="1" class="wide_table">
				<thead id="orconn_list_header">
					<tr>
						<th>status</th>
						<th>name</th>
					</tr>
				</thead>
				<tbody id="orconn_list"></tbody>
			</table>
		</div>

		<h2 id="circuits_title">circuits</h2>
		number of circuits: <span id="circuit_number">0</span>
		<div id="circuit_list_box">
			<table border="1" class="wide_table">
				<thead id="circuit_list_header">
					<tr>
						<th>id</th>
						<th>status</th>
						<th>build flags</th>
						<th>purpose</th>
						<th>time created (UTC)</th>
						<th>path</th>
						<th>HS state</th>
						<th>HS address</th>
						<th>reason</th>
						<th>remote reason</th>
						<th>socks username</th>
						<th>socks password</th>
					</tr>
				</thead>
				<tbody id="circuit_list"></tbody>
			</table>
		</div>

		<h2 id="or_title">ORs</h2>
		number of ORs: <span id="OR_number">0</span>
		<div id="ORlist_box">
			<table border="1" class="wide_table">
				<thead id="ORlist_header">
					<tr>
						<th>nickname</th>
						<th>identity</th>
						<th>digest</th>
						<th>publication (UTC)</th>
						<th>IP (country)</th>
						<th>ORPort</th>
						<th>DirPort</th>
						<th>IPv6 address (country)</th>
						<th>flags</th>
						<th>bandwidth/(KB/s)</th>
						<th>portlist</th>
						<th>version</th>
					</tr>
				</thead>
				<tbody id="ORlist"></tbody>
			</table>
		</div>

		<h2 id="bandwidth_graph_title">bandwidth graph</h2>
		<!-- Only bandwidth_data_size seconds of bandwidth information is stored. -->
		<input type="number" value="10" step="1" min="1"
			onchange="a=this.value;
			bandwidth_graph_px_per_ms=a/1000;
			a=100/a;
			for(b=1;b<6;b++)
				bandwidth_graph_x_numbers[b].innerHTML=(b*a).toFixed(2);">
		<label for="bandwidth_graph_px_per_sec">pixles per second</label>
		<br>
		<svg id="bandwidth_graph" viewBox="0 0 712 436">
			<line x1="90" x2="690" y1="50" y2="50"
				class="bandwidth_graph_reference_line" />
			<line x1="90" x2="690" y1="125" y2="125"
				class="bandwidth_graph_reference_line" />
			<line x1="90" x2="690" y1="200" y2="200"
				class="bandwidth_graph_reference_line" />
			<line x1="90" x2="690" y1="275" y2="275"
				class="bandwidth_graph_reference_line" />
			<line x1="90" x2="690" y1="350" y2="350"
				class="bandwidth_graph_reference_line" />
			<line x1="190" x2="190" y1="40" y2="360"
				class="bandwidth_graph_reference_line" />
			<line x1="290" x2="290" y1="40" y2="360"
				class="bandwidth_graph_reference_line" />
			<line x1="390" x2="390" y1="40" y2="360"
				class="bandwidth_graph_reference_line" />
			<line x1="490" x2="490" y1="40" y2="360"
				class="bandwidth_graph_reference_line" />
			<line x1="590" x2="590" y1="40" y2="360"
				class="bandwidth_graph_reference_line" />
			<path id="upload_path" />
			<path id="download_path" />
			<rect x="0" y="49" width="90" height="302"
				class="bandwidth_graph_cover" />
			<rect x="90" y="40" width="600" height="320"
				class="bandwidth_graph_frame" />
			<text x="60" y="50" class="bandwidth_graph_label">B/s</text>
			<text x="60" y="125" class="bandwidth_graph_label">B/s</text>
			<text x="60" y="200" class="bandwidth_graph_label">B/s</text>
			<text x="60" y="275" class="bandwidth_graph_label">B/s</text>
			<text x="60" y="350" class="bandwidth_graph_label">B/s</text>
			
			<!-- The order of these numbers is important. -->
			<text x="15" y="350"
				class="bandwidth_graph_label bandwidth_graph_label_y_number">0</text>
			<text x="15" y="275"
				class="bandwidth_graph_label bandwidth_graph_label_y_number">1</text>
			<text x="15" y="200"
				class="bandwidth_graph_label bandwidth_graph_label_y_number">2</text>
			<text x="15" y="125"
				class="bandwidth_graph_label bandwidth_graph_label_y_number">3</text>
			<text x="15" y="50"
				class="bandwidth_graph_label bandwidth_graph_label_y_number">4</text>
			
			<text x="215" y="382" class="bandwidth_graph_label">s</text>
			<text x="315" y="382" class="bandwidth_graph_label">s</text>
			<text x="415" y="382" class="bandwidth_graph_label">s</text>
			<text x="515" y="382" class="bandwidth_graph_label">s</text>
			<text x="615" y="382" class="bandwidth_graph_label">s</text>
			<text x="700" y="382" class="bandwidth_graph_label">s</text>
			
			<!-- The order of these numbers is important. -->
			<text x="675" y="382"
				class="bandwidth_graph_label bandwidth_graph_label_x_number">0</text>
			<text x="560" y="382"
				class="bandwidth_graph_label bandwidth_graph_label_x_number">10.00</text>
			<text x="460" y="382"
				class="bandwidth_graph_label bandwidth_graph_label_x_number">20.00</text>
			<text x="360" y="382"
				class="bandwidth_graph_label bandwidth_graph_label_x_number">30.00</text>
			<text x="260" y="382"
				class="bandwidth_graph_label bandwidth_graph_label_x_number">40.00</text>
			<text x="160" y="382"
				class="bandwidth_graph_label bandwidth_graph_label_x_number">50.00</text>
			
			<text x="100" y="404" class="bandwidth_graph_current_download_rate">current download rate:</text>
			<text x="300" y="404" class="bandwidth_graph_current_download_rate"
				id="bandwidth_graph_current_download_rate_number">0</text>
			<text x="376" y="404" class="bandwidth_graph_current_download_rate">B/s</text>
			<text x="100" y="422" class="bandwidth_graph_current_upload_rate">current upload rate:</text>
			<text x="300" y="422" class="bandwidth_graph_current_upload_rate"
				id="bandwidth_graph_current_upload_rate_number">0</text>
			<text x="376" y="422" class="bandwidth_graph_current_upload_rate">B/s</text>
		</svg>

		<h2 id="message_log_title">message log</h2>
		show severities: <input type="checkbox" id="messages_severity_0"
			onchange="update_messages_display(0,this.checked);"> <label
			for="messages_severity_0">INFO</label> <input type="checkbox"
			id="messages_severity_1"
			onchange="update_messages_display(1,this.checked);" checked> <label
			for="messages_severity_1">NOTICE</label> <input type="checkbox"
			id="messages_severity_2"
			onchange="update_messages_display(2,this.checked);" checked> <label
			for="messages_severity_2">WARN</label> <input type="checkbox"
			id="messages_severity_3"
			onchange="update_messages_display(3,this.checked);" checked> <label
			for="messages_severity_3">ERR</label> <br>
		<button type="button"
			onclick="var a;
			while(a=messages_table_tbody.firstChild)
				messages_table_tbody.removeChild(a);">clear</button>
		<div id="messages_table_box">
			<table border="1" id="messages_table">
				<thead id="messages_table_header">
					<tr>
						<th>time</th>
						<th>severity</th>
						<th>message</th>
					</tr>
				</thead>
				<tbody id="messages_table_tbody"></tbody>
			</table>
		</div>

		<h1 id="new_identity">new identity</h1>
		<button type="button" onclick="custom_command_popup('signal newnym');">New
			Identity</button>

		<h1 id="custom_command">custom command</h1>

		<div id="custom_command_console"></div>

		<button type="button"
			onclick="
			var a;
			while(a=custom_command_console.firstChild)
				custom_command_console.removeChild(a);">clear</button>

		<br> <input type="text" id="custom_command_input_box"
			onkeydown="custom_command_handle_key(event);">

		<h1 id="tor_settings">tor settings</h1>

		<label for="tor_options_select_category"> show category </label> <select
			id="tor_options_select_category"
			onchange="tor_options_change_category(this.value);">
			<option value="0">GENERAL OPTIONS</option>
			<option value="1">CLIENT OPTIONS</option>
			<option value="2">SERVER OPTIONS</option>
			<option value="3">DIRECTORY SERVER OPTIONS</option>
			<option value="4">DERECTORY AUTHORITY SERVER OPTIONS</option>
			<option value="5">HIDDEN SERRVICE OPTIONS</option>
			<option value="6">TESTING NETWORK OPTIONS</option>
			<option value="7">ALL OPTIONS</option>
		</select>
		<table border="1">
			<thead id="tor_settings_header">
				<tr>
					<th>name</th>
					<th>type</th>
					<th colspan="3">value</th>
					<th>description</th>
				</tr>
			</thead>
<?php
foreach ( $tor_options_name as $a => $b ) {
	$type = $tor_options_type [$a];
	?>
				<tbody class="tor_options_table_row">
				<tr>
					<td class="tor_options_name">
							<?=$b?>
						</td>
					<td>
							<?=$type?>
						</td>
					<td><input type="checkbox" class="tor_options_default_checkbox"
						id="tor_options_default_checkbox_<?=$a?>"
						<?php if($tor_options_value[$a]===false)echo 'checked';?>></td>
					<td><label for="tor_options_default_checkbox_<?=$a?>"> use default
					</label></td>
					<td>
<?php
	if (($value = $tor_options_value [$a]) === false)
		$value = '';
	switch ($type) {
		case 'Integer' :
			echo '<input class="tor_options_value" value="', $value, '" type="number" min="0">';
			break;
		case 'SignedInteger' :
			echo '<input class="tor_options_value" value="', $value, '" type="number">';
			break;
		case 'Boolean' :
			echo '<select class="tor_options_value"><option value="0"';
			if ($value === '1')
				echo '>0</option><option value="1" selected';
			elseif ($value === '0')
				echo ' selected>0</option><option value="1"';
			else
				echo '>0</option><option value="1"';
			echo '>1</option></select>';
			break;
		case 'Boolean+Auto' :
			echo '<select class="tor_options_value"><option value="auto"';
			if ($value === 'auto')
				echo ' selected>auto</option><option value="0">0</option><option value="1"';
			elseif ($value === '0')
				echo '>auto</option><option value="0" selected>0</option><option value="1"';
			elseif ($value === '1')
				echo '>auto</option><option value="0"></option>0<option value="1" selected';
			else
				echo '>auto</option><option value="0"></option>0<option value="1"';
			echo '>1</option></select>';
			break;
		default :
			echo '<input class="tor_options_value" type="text" value="', $value, '">';
	}
	?>
						</td>
<?php
	if (isset ( $tor_options_description [$b] )) {
		?>
							<td><a class="tor_option_show_description"
						id="tor_options_show_description_<?=$a?>"
						href="javascript:void(0)"
						onclick="tor_options_description_<?=$a?>.style.display='table-row';
									tor_options_hide_description_<?=$a?>.style.display='inline';
									this.style.display='none';">show description</a> <a
						class="tor_option_hide_description"
						id="tor_options_hide_description_<?=$a?>"
						href="javascript:void(0)"
						onclick="tor_options_description_<?=$a?>.style.display='none';
									tor_options_show_description_<?=$a?>.style.display='inline';
									this.style.display='none';">hide description</a></td>
				</tr>
				<tr id="tor_options_description_<?=$a?>"
					class="tor_option_description_block">
					<td colspan="6">
								<?=$tor_options_description[$b]?>
							</td>
<?php
	} else
		echo '<td>no description avilable</td>';
	?>
					</tr>
			</tbody>
<?php
}
?>
		</table>
		<button type="button" onclick="update_settings_handle();">update settings</button>
		<button type="button" onclick="custom_command_popup('saveconf');">
			save settings to torrc</button>

		<h1 id="controller_settings">PHP tor Controller settings</h1>

		<h2 id="connecting_to_tor">connecting to Tor</h2>
		<form method="post">
			<input type="hidden" name="action" value="change_connection_method">
			<table border="1">
				<tbody>
					<tr>
						<td><label for="connection_method"> connection method </label></td>
						<td><select id="connection_method" name="connection_method"
							onchange="if(this.value=='tcp'){connection_tcp.style.display='table-row-group';connection_unix_socket.style.display='none';}else{connection_tcp.style.display='none';connection_unix_socket.style.display='table-row-group';}">
								<option value="tcp"
									<?php if($tc_connection_method==tc_connection_method_network)echo 'selected';?>>tcp</option>
								<option value="unix_socket"
									<?php if($tc_connection_method==tc_connection_method_unix_socket)echo 'selected';?>>unix
									socket</option>
						</select></td>
					</tr>
				</tbody>
				<tbody id="connection_tcp"
					<?php if($tc_connection_method!=tc_connection_method_network)echo 'style="display:none;"';?>>
					<tr>
						<td><label for="connection_tcp_hostname"> host </label></td>
						<td><input id="connection_tcp_hostname"
							name="connection_tcp_hostname" type="text"
							value="<?=htmlspecialchars($connection_tcp_hostname)?>"></td>
					</tr>
					<tr>
						<td><label for="connection_tcp_port"> port </label></td>
						<td><input id="connection_tcp_port" name="connection_tcp_port"
							type="text" value="<?=$connection_tcp_port?>"></td>
					</tr>
					<tr>
						<td><label for="connection_tcp_secure"> ssl or tls </label></td>
						<td><select id="connection_tcp_secure"
							name="connection_tcp_secure">
								<option value="none"
									<?php if($connection_tcp_secure==tc_connection_secure_none)echo 'selected';?>>none</option>
								<option value="ssl"
									<?php if($connection_tcp_secure==tc_connection_secure_ssl)echo 'selected';?>>ssl</option>
								<option value="tls"
									<?php if($connection_tcp_secure==tc_connection_secure_tls)echo 'selected';?>>tls</option>
						</select></td>
					</tr>
				</tbody>
				<tbody id="connection_unix_socket"
					<?php if($tc_connection_method!=tc_connection_method_unix_socket)echo 'style="display:none;"';?>>
					<tr>
						<td><label for="connection_unix_socket_path"> path </label></td>
						<td><input id="connection_unix_socket_path"
							name="connection_unix_socket_path" type="text"
							value="<?=htmlspecialchars($connection_unix_socket_path)?>"></td>
					</tr>
				</tbody>
				<tbody>
					<tr>
						<td><label for="connection_auth_method"> auth method </label></td>
						<td><select id="connection_auth_method"
							name="connection_auth_method"
							onchange="if(this.value=='none'){connection_auth_method_password.style.display='none';connection_auth_method_cookie.style.display='none';}else if(this.value=='password'){connection_auth_method_password.style.display='table-row-group';connection_auth_method_cookie.style.display='none';}else{connection_auth_method_password.style.display='none';connection_auth_method_cookie.style.display='table-row-group';}">
								<option value="none"
									<?php if($tc_connection_auth==tc_connection_auth_none)echo 'selected';?>>none</option>
								<option value="password"
									<?php if($tc_connection_auth==tc_connection_auth_password)echo 'selected';?>>password</option>
								<option value="cookie"
									<?php if($tc_connection_auth==tc_connection_auth_cookie)echo 'selected';?>>cookie</option>
						</select></td>
					</tr>
				</tbody>
				<tbody id="connection_auth_method_password"
					<?php if($tc_connection_auth!=tc_connection_auth_password)echo 'style="display:none;"';?>>
					<tr>
						<td><label for="connection_auth_method_password_password">
								password </label></td>
						<td><input id="connection_auth_method_password_password"
							name="connection_auth_method_password_password" type="password"
							value="<?=htmlspecialchars($connection_auth_password)?>"></td>
					</tr>
					<tr>
						<td><label for="connection_auth_method_password_show_password">
								show password </label></td>
						<td><input type="checkbox"
							id="connection_auth_method_password_show_password"
							onchange="if(this.checked)connection_auth_method_password_password.type='text';else connection_auth_method_password_password.type='password';">
						</td>
				
				</tbody>
				<tbody id="connection_auth_method_cookie"
					<?php if($tc_connection_auth!=tc_connection_auth_cookie)echo 'style="display:none;"';?>>
					<tr>
						<td><label for="connection_auth_method_cookie_cookie_path"> cookie
								path </label></td>
						<td><input id="connection_auth_method_cookie_cookie_path"
							name="connection_auth_method_cookie_path" type="text"
							value="<?=htmlspecialchars($connection_auth_cookie)?>"></td>
					</tr>
				</tbody>
			</table>
			<input type="submit" value="update">
		</form>

		<h2 id="loggine_in">logging in</h2>
		<form method="post">
			<input type="hidden" name="action" value="change_login_method">
			<table border="1">
				<tbody>
					<tr>
						<td><label for="login_method_require_login"> require logging in </label>
						</td>
						<td><input id="login_method_require_login"
							name="login_method_require_login" type="checkbox"
							value="login_method_require_login"
							onchange="if(this.checked)login_method_more.style.display='table-row-group';else login_method_more.style.display='none';"
							<?php if($require_login)echo 'checked';?>></td>
					</tr>
				</tbody>
				<tbody id="login_method_more"
					<?php if(!$require_login)echo 'style="display:none;"';?>>
					<tr>
						<td><label for="login_method_password"> password </label></td>
						<td><input id="login_method_password" name="login_method_password"
							type="password"></td>
					</tr>
				</tbody>
			</table>
			<input type="submit" value="update">
		</form>

	</div>
</body>
</html>
