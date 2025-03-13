<?php

/**
 * controller_mua.inc.php
 *
 * @see        https://sourceforge.net/projects/phpmailerpro/
 *
 * @category   Email Transport
 * @package    PHPMailerPro
 * @author     Andy Prevost <andy@codeworxtech.com>
 * @copyright  2004-2023 (C) Andy Prevost - All Rights Reserved
 * @version    2024.1.3.0
 * @requires   PHP version 8.0.0 (and up)
 * @license    MIT - Distributed under the MIT License shown here:
 *
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the 'Software'), to deal in the Software without
 * restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies
 * of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 **/
/* Last updated: 21 Mar 2024 @ 16:30 (EST) */

namespace codeworxtech\PHPMailerPro;

if (version_compare(PHP_VERSION, "8.0.0", "<=")) {
    exit("Sorry, this version of PHPMailer Pro will only run on PHP version 8.0.0 or greater!\n");
}

class PHPMailerPro
{

    /* CONSTANTS */
    const CRLF = "\r\n";
    const ERR_CONTINUE = 1; // echo message, process ok to continue
    const ERR_CRITICAL = 9; // echo message, process die critical error
    const MAILSEP = ", ";
    const VERSION = "2024.1.3.0";

    /* SMTP CONSTANTS */
    const FAILMK = "&#10007; "; //X (fail, used in error messages)
    const PASSMK = "&#10003; "; //checkmark (ok, used in error messages)
    const TIMEVAL = 30; // seconds

    /* PROPERTIES, PRIVATE & PROTECTED */
    private array $allRecipients = [];
    private array $attachments = [];
    private string $bcc = "";
    private string $bodyMsgPt = "";
    private array $boundary = [];
    private array $bholder = [];
    private string $cc = "";
    private string $confirmReadTo = "";
    private array $customHeader = [];
    private int $errorCount = 0;
    private bool $exceptions = true;
    protected array $language = [];
    private string $msgType = "";
    private string $replyTo = "";
    private string $signCertFile = "";
    private string $signKeyFile = "";
    private string $signKeyPass = "";
    private array $smtpFeedback = [];
    private $smtpStream = 0;
    private array $transports = ["smtp", "sendmail", "imap"];
    private string $to = "";

    /* PROPERTIES, PUBLIC */

    /**
     * Sets message CharSet
     * @var string
     */
    public string $charSet = "UTF-8";

    /**
     * Sets message Content-type
     * @var string
     */
    public string $contentType = "text/plain";

    /**
     * Required: Used with DKIM DNS Resource Record
     * syntax: (base domain) 'yourdomain.com'
     * @var string
     */
    public string $dkimDomain = "";

    /**
     * Optional: Used with DKIM DNS Resource Record
     * syntax: 'you@yourdomain.com'
     * @var string
     */
    public string $dkimIdentity = "";

    /**
     * Optional: Used with DKIM Digital Signing process
     * @var string
     */
    public string $dkimPassphrase = "";

    /**
     * Required: Used with DKIM DNS Resource Record
     * private key (read from /.htkey_private)
     * @var string
     */
    public string $dkimPrivate = "";

    /**
     * Used with DKIM DNS Resource Record
     * @var string
     */
    public string $dkimSelector = "PHPMailerPro";

    /**
     * Sets message Encoding. Options:
     *   "8bit", "7bit", "binary", "base64", and "quoted-printable"
     * @var string
     */
    public string $encoding = "base64";

    /**
     * Most recent mailer error message
     * @var string
     */
    public string $errorInfo = "";

    /**
     * Sets the hostname to use in Message-Id and Received headers
     * and as default HELO string. If empty, the value returned
     * by SERVER_NAME is used or 'localhost.localdomain'
     * @var string
     */
    public string $hostname = "";

    /**
     * If true, adds email message to spec'd IMAP Sent box
     * @var boolean
     */
    public bool $imapAddToSent = false;

    /**
     * Outbound IMAP mail server name (ie. mail.yourserver.com)
     * @var string
     */
    public string $imapHost = "";

    /**
     * Outbound IMAP mail server port
     * @var string
     */
    public string $imapPort = "143/imap/notls";

    /**
     * Outbound IMAP mail server username (ie. name@yourserver.com)
     * @var string
     */
    public string $imapUsername = "";

    /**
     * Outbound IMAP mail server password
     * @var string
     */
    public string $imapPassword = "";

    /**
     * Sets HTML portion of message Body
     * Sets email to multipart/alternative.
     * @var string
     */
    public string $messageHTML = "";

    /**
     * Sets iCalendar/ICS message.
     * @var string
     */
    public string $messageICal = "";

    /**
     * Sets text-only body.
     * Sets email to multipart/alternative.
     * @var string
     */
    public string $messageText = ""; //"To view the message, please use an HTML compatible email viewer!\n";

    /**
     * Container for entire mail message
     * @var string
     */
    public string $mimeMail = "";

    /**
     * Email priority (1 = High, 3 = Normal, 5 = low)
     * Not sent when set to 0 (default)
     * @var int
     */
    public int $priority = 0;

    /**
     * email Return-Path. If not empty, sent via -f or in headers
     * @var string
     */
    public string $returnPath = "";

    /**
     * Container for all recipients
     * @var string
     */
    public string $sender = "";

    /**
     * Sets message From email address
     * @var string
     */
    public string $senderEmail = "root@localhost";

    /**
     * Sets message From name
     * @var string
     */
    public string $senderName = "No Reply";

    /**
     * Provides the ability to have the TO field process individual
     * emails, instead of sending to entire TO addresses
     * @var bool
     */
    public bool $sendIndividualEmails = true;

    /**
     * Default path of sendmail on the server
     * @var string
     */
    public string $sendmailServerPath = "";

    /**
     * Sets message Subject
     * @var string
     */
    public string $subject = "";

    /**
     * Default method to send mail
     * Options are 'smtp', 'sendmail', 'imap'
     * NOTE: for Qmail and other sendmail-equivalents set as 'sendmail'
     *       (all include a sendmail wrapper for compatibility)
     * @var string
     */
    public string $transport = "";

    /**
     * Sets word wrapping on the body of the message to a given number
     * of characters.
     * @var int
     */
    public int $wordWrapLen = 70;

    /* SMTP PROPERTIES, PRIVATE & PROTECTED */
    /**
     * Contains SMTP account: username and password
     * @var array
     */
    public array $smtpAccount = [];

    /**
     * Debug level
     * @var int
     */
    public int|bool $smtpDebug = 0;

    /**
     * Account domain (top level domain)
     * @var string
     */
    public string $smtpDomain = "";

    /**
     * SMTP Mail-From: (email address only)
     * @var string
     */
    public string $smtpFrom = "";

    /**
     * SMTP Host (hostname, MX) example: mail.yourhost.com
     * @var string
     */
    public string $smtpHost = "";

    /**
     * SMTP KeepAlive, triggers a reset if true to prevent the SMTP
     * server from closing
     * @var boolean
     */
    public bool $smtpKeepAlive = false;

    /**
     * Work around to authentication issues
     * @var array
     */
    public array $smtpOptions = []; // ['ssl'=>[ 'verify_peer'=>false, 'verify_peer_name'=>false, 'allow_self_signed'=>true ] ];

    /**
     * SMTP Account password
     * @var string
     */
    public string $smtpPassword = "";

    /**
     * SMTP Port (supports 587, 25, 2525, 465)
     * (Note: many providers have deprecated 465)
     * @var int
     */
    public int $smtpPort = 587;

    /**
     * SMTP Account username
     * @var string
     */
    public string $smtpUsername = "";

    /**
     * VERP = Variable Envelope Return Path (used for bounce handling)
     * @var boolean
     */
    public bool $smtpUseVerp = false;

    /**
     * Class Construct
     */
    public function __construct()
    {
        self::SetLanguage();
        $this->smtpDomain = self::GetMailServer();
        //set boundaries
        $uniqId = md5(uniqid(time()));
        $this->boundary[0] = "P0_" . $uniqId;
        $this->boundary[1] = "P1_" . $uniqId;
        $this->boundary[2] = "P2_" . $uniqId;
    }

    /**
     * Class Destruct
     */
    public function __destruct()
    {
        self::Clear();
        if (self::SMTPisConnected()) {
            self::SMTPquit();
        }
        if ($this->smtpDebug > 0 && count($this->smtpFeedback) > 0) {
            foreach ($this->smtpFeedback as $msg) {
                echo $msg;
            }
        }
    }

    /* METHODS */

    /**
     * Adds an attachment from a path on the filesystem
     * Returns false if the file could not be found
     * or accessed.
     * @param string $path Path to the attachment
     * @param string $name Overrides the attachment name
     * @param string $encoding File encoding (see $Encoding)
     * @param string $type File extension (MIME) type
     * @return bool
     */
    public function AddAttachment($path, $name = "", $encoding = "base64", $type = "")
    {
        try {
            self::IsExploitPath($path, true);
            if (!@is_file($path)) {
                throw new Exception($this->language['file_access'] . $path . "<br>" . self::CRLF, PHPMailerPro::ERR_CONTINUE);
            }
            $type = ($type == "") ? self::GetMimeType($path) : $type;
            $filename = basename($path);
            $name = ($name == "") ? $filename : $name;
            $this->attachments[] = [0 => $path, 1 => $filename, 2 => $name, 3 => $encoding, 4 => $type, 5 => false, 6 => "attachment", 7 => 0];
        } catch (Exception $e) {
            self::SetError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            echo $e->getMessage() . "\n";
            if ($e->getCode() == PHPMailerPro::ERR_CRITICAL) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds a "Bcc" email address
     * @param string $email
     * @param string $name
     * @return boolean true on success, false if address already used
     */
    public function AddBCC($param)
    {
        $param = self::EmailFormatRFC($param);
        $param = self::EmailCheckDuplicate($param);
        if (!empty($param)) {
            $this->bcc .= ((!empty($this->bcc)) ? self::MAILSEP : "") . $param;
        }
    }

    /**
     * Adds a "Cc" address.
     * @param string $email
     * @param string $name
     * @return boolean true on success, false if address already used
     */
    public function AddCC($param)
    {
        $param = self::EmailFormatRFC($param);
        $param = self::EmailCheckDuplicate($param);
        if (!empty($param)) {
            $this->cc .= ((!empty($this->cc)) ? self::MAILSEP : "") . $param;
        }
    }

    /**
     * Adds a custom header.
     * @return void
     */
    public function AddCustomHeader($custom_header)
    {
        $this->customHeader[] = explode(":", $custom_header, 2);
    }

    /**
     * Adds an embedded attachment.
     * @param string $path Path to the attachment.
     * @param string $cid Content ID of the attachment.
     * @param string $name Overrides the attachment name.
     * @param string $encoding File encoding (see $Encoding).
     * @param string $type File extension (MIME) type.
     * @return bool
     */
    public function AddEmbeddedImage($path, $cid, $name = "", $encoding = "", $type = "", $attach = "")
    {
        $encoding = ($encoding == "") ? $this->encoding : $encoding;
        self::IsExploitPath($path, true);
        if (!@is_file($path)) {
            self::SetError($this->language['file_access'] . $path . "<br>" . self::CRLF);
            return false;
        }
        $type = ($type == "") ? self::GetMimeType($path) : $type;
        $filename = basename($path);
        $name = ($name == "") ? $filename : $name;
        if (!self::IsImageDuplicate(0, $path)) {
            // Append to $attachments array
            $this->attachments[] = [0 => $path, 1 => $filename, 2 => $name, 3 => $encoding, 4 => $type, 5 => false, 6 => "inline", 7 => $cid];
            // Add second copy as downloadable attachment
            if ($attach != "") {
                $mimeType = mime_content_type($path);
                $this->attachments[] = [0 => $path, 1 => $attach, 2 => $attach, 3 => $encoding, 4 => $mimeType, 5 => false, 6 => "attachment", 7 => 0];
            }
            return true;
        }
        return false;
    }

    /**
     * Adds email message to IMAP INBOX.Sent
     * @param string $message (optional)
     * @param string $folder (optional)
     * @param string $options (optional)
     */
    public function AddMessageToSent($folder = "INBOX.Sent", $options = null)
    {
        if (!$this->imapAddToSent) {
            return;
        }
        $mailbox = "{" . $this->imapHost . ":" . $this->imapPort . "}" . $folder;
        if (!empty($this->imapHost) && !empty($this->imapUsername) && !empty($this->imapPassword)) {
            $cnx = @imap_open($mailbox, $this->imapUsername, $this->imapPassword);
            $res = imap_append($cnx, $mailbox, $this->mimeMail, null, date("r"));
            @imap_errors();
            @imap_alerts();
            @imap_close($cnx);
        }
    }

    /**
     * Adds a "To" address.
     * @param string $address
     * @param string $name
     * @return boolean true on success, false if address already used
     */
    public function AddRecipient($param)
    {
        //error_log("checking recipient $param");
        //$rfc = self::EmailFormatRFC($param);
        //$rfc = self::EmailCheckDuplicate($rfc);
        $this->to .= ((!empty($this->to)) ? self::MAILSEP : "") . $param;
    }

    /**
     * Adds a "Reply-to" address.
     * @param string $email
     * @param string $name
     * @return boolean
     */
    public function AddReplyTo($param)
    {
        $this->replyTo = self::EmailFormatRFC($param);
    }

    /**
     * Adds a string or binary attachment (non-filesystem) to the list.
     * This method can be used to attach ascii or binary data,
     * such as a BLOB record from a database.
     * @param string $string String attachment data.
     * @param string $filename Name of the attachment.
     * @param string $encoding File encoding (see $Encoding).
     * @param string $type File extension (MIME) type.
     * @return void
     */
    public function AddStringAttachment($string, $filename, $encoding = "base64", $type = "")
    {
        $type = ($type == "") ? self::GetMimeType($string, "string") : $type;
        $this->attachments[] = [
            0 => $string,
            1 => $filename,
            2 => basename($filename),
            3 => $encoding,
            4 => $type,
            5 => true,
            6 => "attachment",
            7 => 0
        ];
    }

    /**
     * Build attachment
     * @param string $attachment
     * @return string
     */
    private function BuildAttachment($attachment = "", $bkey = 0)
    {
        $mime = $cidUniq = $incl = [];
        // add parameter passed in function
        if (!empty($attachment) && is_string($attachment)) {
            if (self::IsPathSafe($attachment) !== true) {
                return false;
            }
            $mimeType = mime_content_type($attachment);
            $fileContent = file_get_contents($attachment);
            $fileContent = (!empty($this->encode_hdr)) ? chunk_split(base64_encode($fileContent)) : $fileContent;
            $data = "Content-Type: " . $mimeType . "; name=" . basename($attachment) . self::CRLF;
            $data .= (!empty($this->encode_hdr)) ? "Content-Transfer-Encoding: " . $this->encode_hdr . self::CRLF : "";
            $data .= "Content-ID: <" . basename($attachment) . ">" . self::CRLF;
            $data .= self::CRLF . $fileContent . self::CRLF;
            $data = self::GetBoundary($bkey) . $data;
            $this->tot_attach[] = $attachment;
            return $data;
        }
        // Add all other attachments and check for string attachment
        $bString = $attachment[5];
        if ($bString) {
            $string = $attachment[0];
        } else {
            $path = $attachment[0];
            if (self::IsPathSafe($path) !== true) {
                return false;
            }
        }
        if (in_array($attachment[0], $incl)) {
            return;
        }
        if (@in_array($path, $incl)) {
            return;
        }

        $filename = $attachment[1];
        $name = $attachment[2];
        $encoding = $attachment[3];
        $type = $attachment[4];
        $disposition = $attachment[6];
        $cid = $attachment[7];
        $incl[] = $attachment[0];

        if ($disposition == "inline" && isset($cidUniq[$cid])) {
            return;
        }
        $cidUniq[$cid] = true;

        $mime[] = "Content-Type: " . $type . "; name=\"" . $name . "\"" . self::CRLF;
        if (!empty($encoding)) {
            $mime[] = "Content-Transfer-Encoding: " . $encoding . self::CRLF;
        }

        if ($disposition == "inline") {
            $mime[] = "Content-ID: <" . $cid . ">" . self::CRLF;
        }
        $mime[] = "Content-Disposition: " . $disposition . "; filename=\"" . $name . "\"" . self::CRLF . self::CRLF;

        // Encode as string attachment
        if ($bString) {
            $str = (!empty($encoding)) ? chunk_split(base64_encode($string), $this->wordWrapLen, self::CRLF) : chunk_split($string, $this->wordWrapLen, self::CRLF);
            $mime[] = $str;
            $mime[] = self::CRLF . self::CRLF;
        } else {
            $str = (!empty($encoding)) ? chunk_split(base64_encode(file_get_contents($path)), $this->wordWrapLen, self::CRLF) : chunk_split(file_get_contents($path), $this->wordWrapLen, self::CRLF);
            $mime[] = chunk_split(base64_encode(file_get_contents($path)), $this->wordWrapLen, self::CRLF);
            $mime[] = self::CRLF . self::CRLF;
        }
        $data = implode("", $mime);
        $data = self::GetBoundary($bkey) . $data;
        return $data;
    }

    /**
     * Build message body
     * @return string
     */
    private function BuildBody()
    {
        self::GetMsgType();
        $this->bodyMsgPt = self::GetMsgPart();
        // message only
        if ($this->msgType == "message") {
            return $this->bodyMsgPt;
        }
        // message with attachment
        elseif ($this->msgType == "attachment|message") {
            $this->bholder = ["msg" => 1, "att" => 0, "inl" => NULL];
            $body = str_replace("P0", "P" . $this->bholder["msg"], $this->bodyMsgPt);
            foreach ($this->attachments as $attachment) {
                if ($attachment[6] === "attachment") {
                    $body .= self::BuildAttachment($attachment, $this->bholder['att']);
                }
            }
            $body .= self::GetBoundary($this->bholder['att'], "--");
        }
        // message with attachment
        elseif ($this->msgType == "attachment|inline|message") {
            $this->bholder = ["msg" => 2, "att" => 0, "inl" => 1];
            $body = str_replace("P0", "P" . $this->bholder["msg"], $this->bodyMsgPt);
            foreach ($this->attachments as $attachment) {
                if ($attachment[6] === "inline") {
                    $body .= self::BuildAttachment($attachment, $this->bholder['inl']);
                }
            }
            $body .= self::GetBoundary($this->bholder['inl'], "--");
            $body .= self::CRLF;
            foreach ($this->attachments as $attachment) {
                if ($attachment[6] === "attachment") {
                    $body .= self::BuildAttachment($attachment, $this->bholder['att']);
                }
            }
            $body .= self::GetBoundary($this->bholder['att'], "--");
            $body .= self::CRLF;
        }
        // message with inline
        elseif ($this->msgType == "inline|message") {
            $this->bholder = ["msg" => 1, "att" => NULL, "inl" => 0];
            $body = str_replace("P0", "P" . $this->bholder["msg"], $this->bodyMsgPt);
            // inline
            foreach ($this->attachments as $attachment) {
                if ($attachment[6] === "inline") {
                    $body .= self::BuildAttachment($attachment, $this->bholder['inl']);
                }
            }
        }
        $body = str_replace(self::CRLF . self::CRLF . self::CRLF, self::CRLF . self::CRLF, $body);
        return $body;
    }

    /**
     * Builds message header
     * @return string
     */
    public function BuildHeader()
    {
        $mimeTxt = "This is a multipart message in MIME format." . self::CRLF;
        $messageID = md5((idate("U") - 1000000000) . uniqid()) . "@" . self::ServerHostname();
        if (empty($this->sender)) {
            if ($this->senderEmail == "root@localhost") {
                $this->senderEmail = "noreply@" . self::GetMailServer();
            }
            self::SetSender([$this->senderEmail => $this->senderName]);
        }
        $hdr = "Return-Path: " . ((!empty($this->returnPath)) ? $this->returnPath : $this->sender) . self::CRLF;
        $hdr .= "Date: " . date("r") . self::CRLF;
        $hdr .= "From: " . $this->sender . self::CRLF;
        $hdr .= "Reply-To: " . ((!empty($this->replyTo)) ? $this->replyTo : $this->sender) . self::CRLF;
        $hdr .= (!empty($this->cc)) ? "Cc: " . $this->cc . self::CRLF : "";
        $hdr .= (!empty($this->bcc)) ? "Bcc: " . $this->bcc . self::CRLF : "";
        $hdr .= "Message-Id: <" . $messageID . ">" . self::CRLF;
        $hdr .= "X-Originating-IP: " . $_SERVER['SERVER_ADDR'] . self::CRLF;
        $hdr .= "X-Mailer: PHPMailer Pro v" . PHPMailerPro::VERSION . " " . $this->transport . " (https://phpmailer.pro/)" . self::CRLF;
        $hdr .= ($this->priority !== 0) ? "X-Priority: " . $this->priority . self::CRLF : "";
        if (!empty($this->confirmReadTo)) {
            $hdr .= "X-Confirm-Reading-To: " . $this->confirmReadTo . self::CRLF;
            $hdr .= "Disposition-Notification-To: " . $this->confirmReadTo . self::CRLF;
            $hdr .= "Return-receipt-to: " . $this->confirmReadTo . self::CRLF;
        }
        for ($index = 0; $index < count($this->customHeader); $index++) {
            $hdr .= trim($this->customHeader[$index][0]) . ": " . self::MbEncode(trim($this->customHeader[$index][1])) . self::CRLF;
        }
        if ($this->transport == "smtp" || $this->transport == "sendmail") {
            $hdr .= "To: " . $this->to . self::CRLF;
            $hdr .= "Subject: " . self::MbEncode($this->subject) . self::CRLF;
        }
        $hdr .= "MIME-Version: 1.0" . self::CRLF;
        if (!$this->signKeyFile) {
            if (stripos($this->msgType, "attachment") !== false) {
                $hdr .= self::GetContentTypeHdr("multipart/mixed", 0) . self::CRLF;
                $hdr .= self::CRLF;
                $hdr .= $mimeTxt;
                if (stripos($this->msgType, "inline") !== false) {
                    $hdr .= self::CRLF;
                    $hdr .= self::GetBoundary(0);
                    $hdr .= self::GetContentTypeHdr("multipart/related", 1) . self::CRLF;
                    $hdr .= self::CRLF;
                    $hdr .= self::GetBoundary(1);
                    $hdr .= self::GetContentTypeHdr("multipart/alternative", 2);
                    $hdr .= self::CRLF;
                } else {
                    $hdr .= self::CRLF;
                    $hdr .= self::GetBoundary(0);
                    $hdr .= self::GetContentTypeHdr("multipart/alternative", 1);
                    $hdr .= self::CRLF;
                }
            } elseif (stripos($this->msgType, "inline") !== false) {
                $hdr .= self::GetContentTypeHdr("multipart/related", 0) . self::CRLF;
                $hdr .= $mimeTxt;
                $hdr .= self::CRLF;
                $hdr .= self::GetBoundary(0);
                $hdr .= self::GetContentTypeHdr("multipart/alternative", 1) . self::CRLF;
                $hdr .= self::CRLF;
            } elseif ($this->msgType == "message") {
                $hdr .= self::GetContentTypeHdr("multipart/alternative", 0) . self::CRLF;
                $hdr .= $mimeTxt;
            }
        }
        return $hdr;
    }

    /**
     * Clear all
     */
    public function Clear()
    {
        unset($this->cc);
        unset($this->bcc);
        unset($this->allRecipients);
        unset($this->attachments);
        unset($this->messageHTML);
        unset($this->messageText);
    }

    /**
     * Clears all recipients assigned in the TO array. Returns void.
     * @return void
     */
    public function ClearAddresses()
    {
        $list = explode(",", $this->to);
        foreach ($list as $key => $item) {
            $email = self::EmailExtractEmail($item);
            unset($this->allRecipients[$email]);
        }
        $this->to = "";
    }

    /**
     * Clears all recipients assigned in the TO, CC and BCC
     * array. Returns void.
     * @return void
     */
    public function ClearAllRecipients()
    {
        $this->to = "";
        $this->cc = "";
        $this->bcc = "";
        $this->allRecipients = [];
    }

    /**
     * Clears all previously set filesystem, string, and binary
     * attachments. Returns void.
     * @return void
     */
    public function ClearAttachments()
    {
        $this->attachments = [];
    }

    /**
     * Clears all recipients assigned in the BCC array. Returns void.
     * @return void
     */
    public function ClearBCCs()
    {
        $list = explode(",", $this->bcc);
        foreach ($list as $key => $item) {
            $email = self::EmailExtractEmail($item);
            unset($this->allRecipients[$email]);
        }
        $this->bcc = "";
    }

    /**
     * Clears all recipients assigned in the CC array. Returns void.
     * @return void
     */
    public function ClearCCs()
    {
        $list = explode(",", $this->cc);
        foreach ($list as $key => $item) {
            $email = self::EmailExtractEmail($item);
            unset($this->allRecipients[$email]);
        }
        $this->cc = "";
    }

    /**
     * Clears all custom headers. Returns void.
     * @return void
     */
    public function ClearCustomHeaders()
    {
        $this->customHeader = [];
    }

    /**
     * Clears all recipients assigned in replyTo array. Returns void.
     * @return void
     */
    public function ClearReplyTos()
    {
        $this->replyTo = "";
    }

    /**
     * Adds a "confirmReadingTo" email address
     * @param string $email
     * @param string $name
     * @return boolean true on success, false if address already used
     */
    public function ConfirmReadingTo($param)
    {
        if (!empty($param)) {
            $this->confirmReadTo = self::EmailFormatRFC($param);
        }
    }

    /**
     * Evaluates the message and returns modifications for inline images
     * and backgrounds
     * @return $message
     */
    public function DataToHTML($content, $basedir = "")
    {
        if (is_file($content)) {
            self::IsExploitPath($content, true);
            $thisdir = (dirname($content) != "") ? rtrim(dirname($content), '/') . '/' : '';
            $basedir = ($basedir == "") ? $thisdir : "";
            $content = file_get_contents($content);
        }
        preg_match_all("/(src|background)=\"(.*)\"/Ui", $content, $images);
        if (isset($images[2])) {
            foreach ($images[2] as $i => $url) {
                if (!preg_match('#^[A-z]+://#', $url)) {
                    if ($basedir != "") {
                        $url = rtrim($basedir, '/') . '/' . $url;
                        self::IsExploitPath($url, true);
                    }
                    $filename = basename($url);
                    $directory = dirname($url);
                    $cid = "cid:" . md5($filename);
                    if ($directory == ".") {
                        $directory = "";
                    }
                    $mimeType = mime_content_type($url);
                    if (strlen($directory) > 1 && substr($directory, -1) != '/') {
                        $directory .= '/';
                    }
                    self::IsExploitPath($directory . $filename, true);
                    if (self::AddEmbeddedImage($directory . $filename, md5($filename), $filename, "base64", $mimeType)) {
                        $content = preg_replace("/" . $images[1][$i] . "=\"" . preg_quote($images[2][$i], '/') . "\"/Ui", $images[1][$i] . "=\"" . $cid . "\"", $content);
                    }
                }
            }
        }
        $this->contentType = "text/html";
        $this->messageHTML = $content;
    }

    /**
     * Create the DKIM header, body, as new header
     * @param string $headers_line
     * @param string $subject
     * @param string $body
     */
    public function DKIMadd($headers_line, $subject, $body)
    {
        $DKIMsignatureType = "rsa-sha256";
        $DKIMcanonicalization = "relaxed/simple";
        $DKIMquery = "dns/txt";
        $DKIMtime = time();
        $subject_header = "Subject: $subject";
        $headers = explode(self::CRLF, $headers_line);
        foreach ($headers as $header) {
            if (strpos($header, "From:") === 0) {
                $from_header = $header;
            } elseif (strpos($header, "To:") === 0) {
                $to_header = $header;
            }
        }
        $from = str_replace('|', '=7C', self::DKIMqp($from_header));
        $to = str_replace('|', '=7C', self::DKIMqp($to_header));
        $subject = str_replace('|', '=7C', self::DKIMqp($subject_header));
        $body = self::DKIMbodyC($body);
        $DKIMlen = strlen($body);
        $DKIMb64 = base64_encode(pack("H*", hash('sha256', $body)));
        $ident = ($this->dkimIdentity == '') ? '' : " i=" . $this->dkimIdentity . ";";
        $dkimhdrs = "DKIM-Signature: v=1;" .
            " a=" . $DKIMsignatureType . ";" .
            " q=" . $DKIMquery . ";" .
            " l=" . $DKIMlen . ";" .
            " s=" . $this->dkimSelector . ";" . self::CRLF .
            " t=" . $DKIMtime . ";" .
            " c=" . $DKIMcanonicalization . ";" . self::CRLF .
            " h=From:To:Subject;" . self::CRLF .
            " d=" . $this->dkimDomain . ";" . $ident . self::CRLF .
            " z=$from" . self::CRLF .
            " |$to" . self::CRLF .
            " |$subject;" . self::CRLF .
            " bh=" . $DKIMb64 . ";" . self::CRLF .
            " b=";
        $toSign = self::DKIMheaderC($from_header . self::CRLF . $to_header . self::CRLF . $subject_header . self::CRLF . $dkimhdrs);
        $signed = self::DKIMsign($toSign) . self::CRLF;
        return $dkimhdrs . $signed;
    }

    /**
     * Generate DKIM Body
     * @param string $body
     */
    public function DKIMbodyC($body)
    {
        if ($body == '') {
            return self::CRLF;
        }
        $body = self::FixCRLF($body);
        return str_replace(self::CRLF . self::CRLF, self::CRLF, $body);
    }

    /**
     * Generate DKIM Header
     * @param string $s Header
     */
    public function DKIMheaderC($header)
    {
        $header = self::FixCRLF($header);
        $header = preg_replace("/\r\n\s+/", " ", $header);
        $lines = explode(self::CRLF, $header);
        foreach ($lines as $key => $line) {
            list($heading, $value) = explode(":", $line, 2);
            $heading = strtolower($heading);
            $value = preg_replace("/\s+/", " ", $value);
            $lines[$key] = $heading . ":" . trim($value);
        }
        return implode(self::CRLF, $lines);
    }

    /**
     * Set the private key file and password to sign the message
     * @param string $key_filename Parameter File Name
     * @param string $key_pass Password for private key
     */
    public function DKIMqp($txt)
    {
        $tmp = $line = "";
        for ($i = 0; $i < strlen($txt); $i++) {
            $ord = ord($txt[$i]);
            if (((0x21 <= $ord) && ($ord <= 0x3A)) || $ord == 0x3C || ((0x3E <= $ord) && ($ord <= 0x7E))) {
                $line .= $txt[$i];
            } else {
                $line .= "=" . sprintf("%02X", $ord);
            }
        }
        return $line;
    }

    /**
     * Generate DKIM signature
     * @param string $s Header
     */
    public function DKIMsign($s)
    {
        self::IsExploitPath($this->dkimPrivate, true);
        $privKeyStr = file_get_contents($this->dkimPrivate);
        if ($this->dkimPassphrase != '') {
            $privKey = openssl_pkey_get_private($privKeyStr, $this->dkimPassphrase);
        } else {
            $privKey = $privKeyStr;
        }
        if (openssl_sign($s, $signature, $privKey)) {
            return base64_encode($signature);
        }
    }

    /**
     * Loops through recipients, cc and bcc emails and returns
     * strings with no duplicates.
     * @param string
     * @return string
     */
    private function EmailCheckDuplicate($param)
    {
        $nArr = $tArr = [];
        if (strpos(",", $param)) {
            $tArr = explode(",", $param);
            $tArr = array_map("trim", $tArr);
        } else {
            $tArr[] = $param;
        }
        $dupes = false;
        foreach ($tArr as $key => $addy) {
            $email = self::EmailExtractEmail($addy);
            if (!array_key_exists($email, $this->allRecipients)) {
                $name = self::EmailExtractName($addy);
                $nArr[] = $name . "<" . $email . ">";
                $this->allRecipients[$email] = $name;
            } else {
                $dupes = true;
            }
        }
        return($dupes === true) ? implode(", ", $nArr) : implode(", ", $tArr);
    }

    /**
     * extracts email address from a string
     * returns clean (shell safe) email address (WITHOUT TOKENS)
     * @var string
     * @return string
     */
    private function EmailExtractEmail($str)
    {
        $email = "";
        if (is_string($str)) {
            preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,9}/', $str, $bk);
            if (!empty($bk[0])) {
                $email = $bk[0];
            }
            $email = str_ireplace(["\r", "\n", "\t", '"', ",", "<", ">"], "", $email);
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && self::IsValidEmail($email)) {
                return $email;
            }
        }
        return false;
    }

    /**
     * extracts name portion from string email address
     * returns clean (shell safe) name
     * @var string
     * @return string
     */
    private function EmailExtractName($str)
    {
        if (trim($str) == "") {
            return;
        }
        $pattern = '/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,9}/';
        preg_match($pattern, $str, $match);
        $addy = (!empty($match[0])) ? $match[0] : "";
        return trim(str_ireplace([$addy, "<", ">", "[", "]", "\r", "\n", "\t"], "", $str));
    }

    /**
     * input string or array containing email addresses (separated by comma)
     * in almost any format - can be single address or multiple
     * with or without correct spacing, quote marks
     * removes items without emails
     * returns RFC 5322 formatted string
     * @var string or array
     * @return string
     */
    private function EmailFormatRFC($data)
    {
        $rz = "";
        if (is_string($data)) {
            foreach ((explode(",", $data)) as $key => $val) {
                $val = trim($val);
                preg_match('/[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,9}/', $val, $bk);
                if (filter_var($bk[0], FILTER_VALIDATE_EMAIL)) {
                    $email = trim($bk[0]);
                    $name = $val;
                    if (!empty($email)) {
                        if (!empty($name)) {
                            $rz .= self::EmailExtractName($name) . " ";
                        }
                        $rz .= "<" . self::EmailExtractEmail($email) . ">, ";
                    }
                }
            }
            return rtrim($rz, ", ");
        } else {
            foreach ($data as $key => $val) {
                $name = $email = "";
                if (is_array($val)) {
                    $kkey = trim(str_replace(["<", ">"], "", key($val)));
                    $vval = trim(str_replace(["<", ">"], "", $val[$kkey]));
                    if (filter_var($kkey, FILTER_VALIDATE_EMAIL)) {
                        $email = "<" . $kkey . ">";
                        $name = ($vval != "") ? $vval : "";
                    } elseif (filter_var($vval, FILTER_VALIDATE_EMAIL)) {
                        $email = "<" . $vval . ">";
                        $name = ($kkey != "") ? $kkey : "";
                    }
                    if (!empty($email)) {
                        if (!empty($name)) {
                            $rz .= self::EmailExtractName($name) . " ";
                        }
                        $rz .= "<" . self::EmailExtractEmail($email) . ">, ";
                    }
                } else {
                    if (is_numeric($key) && str_contains($val, "<")) {
                        $t = explode("<", $val);
                        $key = trim($t[0]);
                        $val = trim(str_replace(["<", ">"], "", $t[1]));
                    }
                    $key = trim($key);
                    $val = trim($val);
                    if (filter_var($key, FILTER_VALIDATE_EMAIL)) {
                        $email = $key;
                        $name = ($val != "") ? $val : "";
                    } elseif (filter_var($val, FILTER_VALIDATE_EMAIL)) {
                        $email = $val;
                        $name = (!is_numeric($key) && $key != "") ? $key : "";
                    }
                    if (!empty($email)) {
                        if (!empty($name)) {
                            $rz .= self::EmailExtractName($name) . " ";
                        }
                        $rz .= "<" . self::EmailExtractEmail($email) . ">, ";
                    }
                }
            }
            return rtrim($rz, ", ");
        }
    }

    /**
     * Changes all CRLF or CR to LF
     * @return string
     */
    private function FixCRLF($str)
    {
        return str_ireplace(["\r\n", "\r", "\n"], self::CRLF, $str);
    }

    /**
     * Return the current array of attachments
     * @return array
     */
    private function GetAttachments()
    {
        return $this->attachments;
    }

    /**
     * Creates the boundary line / end boundary line
     * @param string $type = wrap, body, spec, none
     * @param string $end (optional, triggers adding two dashes at end)
     * @return string (boundary line)
     */
    private function GetBoundary($bkey, $end = "")
    {
        return "--" . $this->boundary[$bkey] . $end . self::CRLF;
    }

    /**
     * Creates the Content-Type directive for the body
     * @param string $type = multipart/mixed
     *                       multipart/related
     *                       multipart/alternative
     * @param string $charset
     * @param string $encoding
     * @param string $cid (optional)
     * @return string (content type line)
     */
    private function GetContentTypeBody($type, $charset, $encoding, $cid = "")
    {
        $data = "Content-Type: " . $type . ";" . self::CRLF;
        $data . "\t" . $charset . self::CRLF;
        $data .= "Content-Transfer-Encoding: " . $encoding . self::CRLF;
        if ($cid != "") {
            $data .= "Content-ID: <" . $cid . ">" . self::CRLF;
        }
        return $data;
    }

    /**
     * Creates the Content-Type directive for the header
     * type= multipart/mixed | multipart/related | multipart/alternative
     * bkey= boundary (wrap / body / spec)
     * @return string (content type line)
     */
    private function GetContentTypeHdr($type, $bkey = 0)
    {
        $data = "Content-Type: " . $type . ";" . "\r\n";
        return $data . "\t" . "boundary=\"" . $this->boundary[$bkey] . "\"";
    }

    /**
     * Takes host or path (string) and returns the MX record domain name
     * @return string (mail server)
     */
    private function GetMailServer($url = "")
    {
        if ($url == "") {
            $url = $_SERVER['SERVER_NAME'];
        }
        $bits = parse_url($url);
        if (isset($bits['host'])) {
            $key = "host";
        } elseif (isset($bits['path'])) {
            $key = "path";
        }
        $tld = $bits[$key];
        if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $tld, $match)) {
            getmxrr($match['domain'], $mx_details);
            if (is_array($mx_details) && count($mx_details) > 0) {
                return reset($mx_details);
            }
        }
        return $url;
    }

    /**
     * Gets MIME type of file or string
     * if file: USE ONLY AFTER VERIFYING FILE EXISTS
     * if string: designed for file data read in as string, will not
     *            properly detect html vs text
     * returns 'application/octet-stream' if not found (or file encoded)
     * @param string $resource (filename or string)
     * @param string $type     ('string' or 'file', defaults to 'file')
     * @return string
     */
    public function GetMimeType($resource, $type = "file")
    {
        if ($type == "string") {
            if (function_exists('finfo_buffer') && function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE')) {
                return finfo_buffer(finfo_open(FILEINFO_MIME_TYPE), $resource);
            }
        } else {
            if (function_exists('finfo_file') && function_exists('finfo_open') && defined('FILEINFO_MIME_TYPE')) {
                return finfo_file(finfo_open(FILEINFO_MIME_TYPE), $resource);
            }
            return mime_content_type($resource);
        }
        return "application/octet-stream";
    }

    /**
     * Builds plain text and HTML portion of message
     * @return string
     */
    private function GetMsgPart()
    {
        $data = $html = "";
        if (!empty($this->messageICal)) {
            if (@is_file($this->messageICal)) {
                self::IsExploitPath($this->messageICal, true);
                $thisdir = (dirname($this->messageICal) != "") ? rtrim(dirname($this->messageICal), '/') . '/' : "";
                $string = file_get_contents($thisdir . $this->messageICal);
                $filename = basename($this->messageICal);
            } else {
                $string = $this->messageICal;
                $filename = basename("calendar.ics");
            }
            self::AddStringAttachment($string, $filename, $this->encoding, "text/calendar");
        }
        if (trim($this->messageHTML) != "") {
            if (is_file($this->messageHTML)) {
                self::IsExploitPath($this->messageHTML, true);
                $thisdir = (dirname($this->messageHTML) != "") ? rtrim(dirname($this->messageHTML), '/') . '/' : '';
                self::DataToHTML(file_get_contents($this->messageHTML), $thisdir);
            }
            self::GetMsgType();
            $html .= self::GetBoundary(0);
            $html .= self::GetContentTypeBody("text/html", "charset=\"" . $this->charSet . "\"", $this->encoding) . self::CRLF;
            $html .= self::CRLF;
            //            $html .= chunk_split(base64_encode($this->messageHTML), $this->wordWrapLen, self::CRLF);
            $html .= self::WrapText(base64_encode($this->messageHTML), $this->wordWrapLen) . self::CRLF;
        }
        $data .= self::CRLF;
        $data .= self::GetBoundary(0);
        $data .= self::GetContentTypeBody("text/plain", "charset=\"" . $this->charSet . "\"", "7bit");
        $data .= self::CRLF;
        $data .= ((trim($this->messageText) != "") ? self::WrapText($this->messageText, $this->wordWrapLen) : "") . self::CRLF;
        $data .= self::CRLF;
        $data .= $html;
        $data = str_replace(self::CRLF . self::CRLF . self::CRLF, self::CRLF . self::CRLF, $data);
        return $data . self::GetBoundary(0, "--") . self::CRLF;
    }

    /**
     * Gets email message type
     * @return string
     */
    private function GetMsgType($type = "")
    {
        if (is_string($type) && !empty($type)) {
            $type = explode("|", rtrim($type, "|") . "|");
        } else {
            $type = [];
        }
        if (!in_array("message", $type) && ($this->messageHTML != "" || $this->messageText != "")) {
            $type[] = "message";
        }
        foreach ($this->attachments as $attachment) {
            if ($attachment[6] === "inline" && !in_array("inline", $type)) {
                $type[] = "inline";
            } elseif ($attachment[6] === "attachment" && !in_array("attachment", $type)) {
                $type[] = "attachment";
            }
        }
        if (count($type) == 0) {
            $type[] = "inline";
        }
        sort($type);
        $this->msgType = implode("|", $type);
    }

    /**
     * Returns true if an error occurred
     * @return bool
     */
    public function IsError()
    {
        return($this->errorCount > 0);
    }

    /**
     * Check the attachments array for a duplicate image
     * will not add if duplicate exists
     * @param string $id (attachments[0])
     * @param string $param value
     * @return bool
     */
    function IsImageDuplicate($id, $param)
    {
        if (isset($this->attachments) && ($this->attachments) > 0) {
            foreach ($this->attachments as $key => $val) {
                if ($val[$id] === $param) {
                    return $key;
                }
            }
            return false;
        }
    }

    /**
     * Check if file path is safe (real, accessible).
     * @param string $path Relative or absolute path to a file
     * @return bool
     */
    protected function IsPathSafe($path, $opt_exit = false)
    {
        // path decode (note %00 - null - removed in decode)
        for ($i = 0; $i <= substr_count($path, "%"); $i++) {
            $path = rawurldecode($path);
        }
        // convert all slashes to system default
        $path = str_replace(["\\", "/"], DIRECTORY_SEPARATOR, $path);
        if (!is_dir($path) && file_exists($path)) {
            return true;
        }
        // check for other exploits
        if (self::IsExploitPath($path)) {
            return false;
        }
        // check for path traversal
        if (strpos(realpath($path), $_SERVER['DOCUMENT_ROOT']) === false) {
            if ($opt_exit) {
                throw new Exception($this->language['execute'] . "<br>" . self::CRLF, PHPMailerPro::ERR_CRITICAL);
            }
            return false;
        }
        // check for valid path, path/file
        if (is_file($path)) {
            $path = str_replace(basename($path), "", $path);
        }
        $realPath = str_replace(rtrim($_SERVER['DOCUMENT_ROOT'] . dirname($_SERVER['PHP_SELF']), '/') . '/', "", realpath($path));
        if (strpos($path, '/')) {
            $realPath = rtrim($realPath, '/') . '/';
        }

        if ($path === false) {
            echo "path is false<br>";
        }
        if (strcmp(rtrim($path, '/'), rtrim($realPath, '/')) !== 0) {
            echo "strcmp is false<br>";
        }
        if (($path === false) || (strcmp(rtrim($path, '/'), rtrim($realPath, '/')) !== 0)) {
            return false;
        }
        return(file_exists($path) && is_readable($path) && is_dir($path)) ? true : false;
    }

    /**
     * Prevent attacks by disallowing unsafe shell characters.
     * Modified version (Thanks to Paul Buonopane <paul@namepros.com>)
     * Modification: CRITICAL STOP on NOT safe
     * @param  string  $email (the string to be tested for shell safety)
     * @return bool
     */
    protected function IsShellSafe($email)
    {
        $safe = true;
        if (empty(trim($email))) {
            $safe = false;
        }
        if ($safe && function_exists('ctype_alnum')) {
            for ($i = 0; $i < strlen($email); $i++) {
                $chr = $email[$i];
                if (!ctype_alnum($chr) && strpos('@_-.', $chr) === false) {
                    $safe = false;
                }
            }
        }
        if ($safe) {
            $safe = (bool) preg_match('/\A[\pL\pN._@-]*\z/ui', $email);
        }
        if ($safe === false) {
            exit("Cannot Process: Email Address failed shell safe validation<br>");
        }
        return true;
    }

    /**
     * Encodes and wraps long multibyte strings for mail headers
     * without breaking lines within a character
     * validates $str as multibyte
     * @param string $str multi-byte string to encode
     * @return string
     */
    function MbEncode($str, $len = 70)
    {
        $str = self::SafeStr($str);
        if (mb_strlen($str) != strlen($str)) {
            return $str;
        }
        if (function_exists("mb_internal_encoding") && function_exists("mb_encode_mimeheader")) {
            mb_internal_encoding("UTF-8");
            return mb_encode_mimeheader($str, "UTF-8");
        } else {
            $prefs = ["scheme" => "Q", "input-charset" => "utf-8", "output-charset" => "utf-8", "line-length" => $len];
            return iconv_mime_encode($str, $prefs);
        }
    }

    /**
     * Opens/Closes process for sendmail
     * @param string $sendmail The command
     * @param string $hdr The message headers
     * @param string $body The message body
     * @param string $to (optional for individual emails)
     * @return bool
     */
    private function OutputSendmail($command, $hdr, $body, $to = "")
    {
        $opt = [0 => ["pipe", "r"], 1 => ["pipe", "w"], 2 => ["pipe", "w"]];
        if (function_exists('proc_open')) {
            $hndl = proc_open($command, $opt, $bk);
            if (is_resource($hndl)) {
                fputs($bk[0], $hdr);
                fputs($bk[0], $body . self::CRLF);
                fclose($bk[0]);
                $result = proc_close($hndl);
                if ($result == -1) {
                    throw new Exception($this->language['execute'] . $this->sendmailServerPath . "<br>" . self::CRLF, PHPMailerPro::ERR_CRITICAL);
                } else {
                    return $result;
                }
            }
        } elseif (function_exists('popen')) {
            if (!@$hndl = popen($command, "w")) {
                throw new Exception($this->language['execute'] . $this->sendmailServerPath . "<br>" . self::CRLF, PHPMailerPro::ERR_CRITICAL);
            }
            if ($to != "") {
                fputs($hndl, "To: " . $to . self::CRLF);
            }
            fputs($hndl, $hdr);
            fputs($hndl, $body);
            $result = pclose($hndl);
            if ($result != 0) {
                throw new Exception($this->language['execute'] . $this->sendmailServerPath . "<br>" . self::CRLF, PHPMailerPro::ERR_CRITICAL);
            }
        }
    }

    /**
     * Filter key/value pairs in language file
     * @param string $param String
     * @return string (trimmed)
     */
    protected function SafeKeyVal($param)
    {
        $param = trim($param);
        $param = rtrim($param, ",;");
        return trim($param, '"' . "'");
    }

    /**
     * Filter data (ascii and url-encoded) to prevent header injection
     * @param string $str String
     * @return string (trimmed)
     */
    protected function SafeStr($str)
    {
        return trim(str_ireplace(["\r", "\n", "%0d", "%0a", "Content-Type:", "bcc:", "to:", "cc:"], "", $str));
    }

    /**
     * Creates message and assigns Mailer. If the message is
     * not sent successfully then it returns false. Use the errorInfo
     * variable to view description of the error.
     * @return bool
     */
    public function Send($via = "smtp")
    {

        if ($via != "") {
            $via = strtolower($via);
        }
        if (!in_array($via, $this->transports)) {
            throw new Exception("'" . $via . "' " . $this->language['transport_na'] . self::CRLF, PHPMailerPro::ERR_CRITICAL);
        }
        $this->transport = $via;
        if ($this->to == "" && $this->bcc != "") {
            $this->to = "undisclosed-recipients:";
        }
        try {
            if (empty($this->to) && empty($this->cc) && empty($this->bcc)) {
                throw new Exception($this->language['provide_address'] . self::CRLF, PHPMailerPro::ERR_CRITICAL);
            }
            $this->errorCount = 0;
            $body = self::BuildBody();
            $hdr = self::BuildHeader();
            $this->mimeMail = $hdr . self::CRLF . $body;
            if ($this->dkimDomain && $this->dkimPrivate) {
                $header_dkim = self::DKIMadd($hdr, $this->subject, $body);
                $hdr = self::FixCRLF($header_dkim) . $hdr;
            }
            $retSend = false;
            if ($this->transport == "smtp") {
                if ($this->smtpUsername == "" && count($this->smtpAccount) > 1) {
                    $this->smtpUsername = $this->smtpAccount[0];
                    $this->smtpPassword = $this->smtpAccount[1];
                }
                $retSend = self::TransportSMTP($hdr, $body);
                if ($retSend === false && $this->smtpUsername != "" && $this->smtpPassword != "") {
                    return false;
                } elseif ($retSend === true) {
                    self::AddMessageToSent("INBOX.Sent");
                    return true;
                }
            }
            if (
                $this->transport == "sendmail" ||
                ($this->transport == "smtp" && $retSend === false)
            ) {
                $hdr = str_replace(" sendmail (https://phpmailer.pro/)", " (https://phpmailer.pro/)", $hdr);
                $hdr = str_replace(" (https://phpmailer.pro/)", " sendmail (https://phpmailer.pro/)", $hdr);
                $retSend = self::TransportSendmail($hdr, $body);
                if ($retSend === true) {
                    self::AddMessageToSent("INBOX.Sent");
                    return true;
                }
            }
            if (
                $this->transport == "imap" ||
                ($this->transport == "sendmail" || $this->transport == "smtp" && $retSend === false)
            ) {
                if (strpos("imap", $hdr) === false) {
                    $hdr = str_replace(" (https://phpmailer.pro/)", " imap (https://phpmailer.pro/)", $hdr);
                }
                $retSend = self::TransportIMAP($hdr, $body);
                if ($retSend === true) {
                    self::AddMessageToSent("INBOX.Sent");
                    return true;
                }
            }
            return false;
        } catch (Exception $e) {
            self::SetError($e->getMessage());
            if ($this->exceptions) {
                throw $e;
            }
            echo $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Returns server hostname or 'localhost.localdomain' if unknown
     * @return string
     */
    private function ServerHostname()
    {
        if (!empty($this->hostname)) {
            return $this->hostname;
        } elseif (isset($_SERVER['SERVER_NAME'])) {
            return $_SERVER['SERVER_NAME'];
        }
        return "localhost.localdomain";
    }

    /**
     * Set/Reset Class Objects (variables)
     * Usage Example:
     * $mail->set('X-Priority', '3');
     * @param string $name Parameter Name
     * @param mixed $value Parameter Value
     */
    public function Set($name, $value = "")
    {
        if ($name == "" && $value == "") {
            return true;
        }
        try {
            if (isset($this->$name)) {
                $this->$name = $value;
            } else {
                throw new Exception($this->language['variable_set'] . $name . "<br>" . self::CRLF, PHPMailerPro::ERR_CRITICAL);
            }
        } catch (Exception $e) {
            self::SetError($e->getMessage());
            if ($e->getCode() == PHPMailerPro::ERR_CRITICAL) {
                return false;
            }
        }
        return true;
    }

    /**
     * Adds the error message to the error container
     * @return void
     */
    protected function SetError($msg)
    {
        $this->errorCount++;
        $this->errorInfo = $msg;
    }

    /**
     * Sets the language for all class error messages
     * Default language is English en
     * @param string $langcode
     * @param string $lang_path Path to the language file directory
     */
    public function SetLanguage($langcode = "en", $lang_path = "language/")
    {
        $lang_arr = [
            'authenticate' => "SMTP Error: Could not authenticate.",
            'authenticated' => "Authentication initiated",
            'connect_host' => "SMTP Error: Could not connect to SMTP host.",
            'connected' => "Connection established",
            'connection_error' => "SMTP connection error, aborting",
            'connection_replies' => "Connection &amp; replies verified",
            'data_not_accepted' => "SMTP Error: Data not accepted.",
            'email_send' => "Unable to send e-mail, error: ",
            'empty_message' => "Message body empty",
            'encoding' => "Unknown encoding: ",
            'execute' => "Could not execute: ",
            'file_access' => "Could not access file: ",
            'file_open' => "File Error: Could not open file: ",
            'from_failed' => "The following From address failed: ",
            'instantiate' => "Could not instantiate mail function.",
            'invalid_address' => "Invalid address",
            'invalid_protocol' => "Invalid SMTP Protocol or SMTP Port",
            'keep_alive_accepted' => "Keep Alive (RSET) sent and accepted",
            'keep_alive_error' => "Called Keep Alive without connection.",
            'MAIL_FROM' => "MAIL FROM sent and accepted",
            'not_connected' => "Critical error: not connected to SMTP server.",
            'password_accepted' => "Password accepted",
            'provide_address' => "You must provide at least one recipient email address.",
            'RCPT_TO' => "RCPT TO sent and accepted",
            'recipients_failed' => "SMTP Error: The following recipients failed: ",
            'server_response' => "Error while fetching server response.",
            'signing' => "Signing Error: ",
            'SMTPconnect_failed' => "SMTP connect() failed.",
            'STARTTLS' => "STARTTLS initiated",
            'transfer_accepted' => "Message transfer accepted",
            'transfer_completed' => "Email transfer completed and connection closed",
            'transfer_started' => "Data transfer initiated",
            'transport_na' => "Transport Not Available",
            'tls' => "SMTP secure error: invalid tls or ssl",
            'unknown' => "unknown: ",
            'username_accepted' => "Username accepted",
            'variable_set' => "Cannot set or reset variable: "
        ];
        // optional use of PHPMailer language files
        if ($langcode != "en" && file_exists($lang_path . "phpmailer.lang-" . $langcode . ".php")) {
            // sanitize and validate data in language file
            $nln = "";
            $lines = file($lang_path . "phpmailer.lang-" . $langcode . ".php");
            foreach ($lines as $l) {
                $skip = ['<?php', '?>', '/*', '*', '*/', '//'];
                $l = str_replace(['$PHPMAILER_LANG', '[', ']'], "", $l);
                $l = str_replace('=>', '=', $l);
                foreach ($skip as $ele) {
                    if (strncmp(mb_strtolower(trim($l)), mb_strtolower($ele), strlen(mb_strtolower($ele))) === 0) {
                        $l = "";
                    }
                }
                if (trim($l) != "" && strlen(trim($l)) > 3 && str_contains($l, "=")) {
                    $bits = explode("=", $l);
                    $key = self::safeKeyVal($bits[0]);
                    $val = addslashes(self::safeKeyVal($bits[1]));
                    if (array_key_exists($key, $lang_arr)) {
                        $lang_arr[$key] = $val;
                    }
                }
            }
        }
        $this->language = $lang_arr;
        return true;
    }

    /**
     * Set the senderEmail and senderName properties
     * @param string $email
     * @param string $name
     * @return boolean
     */
    public function SetSender($data)
    {
        $this->sender = self::EmailFormatRFC($data);
        $email = self::EmailExtractEmail($this->sender);
        $name = self::EmailExtractName($this->sender);
        $this->smtpFrom = $email;
        $this->senderEmail = $email;
        $this->senderName = $name;
        $this->returnPath = $this->sender;
    }

    /**
     * Set subject
     * @param string $subject The subject of the email
     */
    public function SetSubject($subject)
    {
        $this->subject = self::MbEncode(self::SafeStr($subject));
    }

    /**
     * Sets SMTP Username and password
     * @return mixed
     */
    public function SetSMTPaccount($array)
    {
        $password = trim(reset($array));
        $username = (is_numeric(key($array))) ? $password : trim(key($array));
        if (trim($password) == "" || trim($username) == "") {
            throw new Exception($this->language['authenticate'] . "<br>" . self::CRLF, PHPMailerPro::ERR_CRITICAL);
        }
        $this->smtpUsername = $username;
        $this->smtpPassword = $password;
    }

    /**
     * Sets message type to HTML
     * DEPRECATED, WILL BE REMOVED IN FUTURE RELEASE
     * @param bool
     * @return void
     */
    public function SetTypeHTML($is_type_html = true)
    {
        $this->contentType = ($is_type_html) ? "text/html" : "text/plain";
    }

    /**
     * Set the private key file and password to sign the message
     * @param string $key_filename Parameter File Name
     * @param string $key_pass Password for private key
     */
    public function Sign($cert_filename, $key_filename, $key_pass)
    {
        $this->signCertFile = $cert_filename;
        $this->signKeyFile = $key_filename;
        $this->signKeyPass = $key_pass;
    }

    /* IMAP transport ONLY
     * Security to ALL the data and email addresses
     * must occur BEFORE calling this function
     * @return bool
     */
    protected function TransportIMAP($hdr, $body)
    {
        if (empty(trim($this->sender)) || empty(trim($this->to))) {
            return false;
        }
        if ($this->mimeMail == "") {
            $this->mimeMail = $hdr . self::CRLF . $body;
        }
        $to = $this->to;
        $subject = self::MbEncode($this->subject);
        $cc = (!empty($this->cc)) ? $this->cc : null;
        $bcc = (!empty($this->bcc)) ? $this->bcc : null;
        $rpath = $this->sender;
        $ret = imap_mail($to, $subject, $body, $hdr, $cc, $bcc, $rpath);
        return $ret;
    }

    /**
     * Sends mail using the Sendmail program
     * @param string $hdr The message headers
     * @param string $body The message body
     * @return bool
     */
    protected function TransportSendmail($hdr, $body)
    {
        if (empty($this->sendmailServerPath) && trim(ini_get('sendmail_path')) != "") {
            $this->sendmailServerPath = ini_get('sendmail_path');
        }
        $this->sendmailServerPath = str_replace([" -o "," -i "]," -oi ", $this->sendmailServerPath);
        $command = $this->sendmailServerPath . " -f" . self::EmailExtractEmail($this->sender);
        if ($this->sendIndividualEmails === true) {
            $emailArr = [];
            if (strpos(",", $this->to)) {
                $emailArr = explode(",", $this->to);
                $emailArr = array_map("trim", $emailArr);
            } else {
                $emailArr[] = $this->to;
            }
            foreach ($emailArr as $key => $val) {
                self::OutputSendmail($command, $hdr, $body, $val);
            }
        } else {
            self::OutputSendmail($command, $hdr, $body);
        }
        return true;
    }

    /**
     * Sends mail via SMTP
     * exit() if there is a bad MAIL FROM, RCPT, or DATA input
     * @param string $hdr  Email headers
     * @param string $body Email message
     * @return bool
     */
    protected function TransportSMTP($hdr, $body)
    {
        if (self::SMTPconnect() === false) {
            $this->transport = "sendmail";
            return false;
        }
        self::SMTPrecipient($this->to);
        self::SMTPdata($hdr, $body);
        if ($this->smtpKeepAlive == true) {
            self::SMTPreset();
        }
        return true;
    }

    /**
     * Check if path (file) exists and is readable
     * @var string $path
     * @return bool
     */
    private function IsExploitPath($path, $optExit = false)
    {
        $isExploit = (is_file($path) && is_readable($path)) ? false : true;
        if ($isExploit && $optExit !== false) {
            throw new Exception($this->language['execute'] . "<br>" . self::CRLF, PHPMailerPro::ERR_CRITICAL);
        }
        return $isExploit;
    }

    /**
     * Validate an email address, probably the most robust validator available
     * @param string $email The email address to check
     * @return boolean
     */
    private function IsValidEmail($email)
    {
        $temp = explode("@", $email);
        $domn = array_pop($temp);
        $rz = true;
        if ($rz) {
            $rz = false;
            $check = @fsockopen($domn, 80, $errno, $errstr, 1);
            if ($check) {
                $rz = true;
                @fclose($check);
            }
        }
        if ($rz) {
            $rz = (bool) preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD', $email);
        }
        return $rz;
    }
    /**
     * Wraps message for use with mailers that do not automatically
     * perform wrapping
     * @param string $message The message to wrap
     * @param integer $length The line length to wrap to
     * @return string
     */
    public function WrapText($message, $length)
    {
        $message = self::FixCRLF($message);
        if (substr($message, -1) == self::CRLF) {
            $message = substr($message, 0, -1);
        }
        $line = explode(self::CRLF, $message);
        $message = "";
        for ($i = 0; $i < count($line); $i++) {
            $line_part = explode(" ", $line[$i]);
            $buf = "";
            for ($e = 0; $e < count($line_part); $e++) {
                $word = $line_part[$e];
                $buf_o = $buf;
                $buf .= ($e == 0) ? $word : (" " . $word);

                if (strlen($buf) > $length and $buf_o != "") {
                    $message .= $buf_o . "\n";
                    $buf = $word;
                }
            }
            $message .= $buf . self::CRLF;
        }
        return $message;
    }

    /* SMTP METHODS ************/

    /**
     * Connect to the server
     * return code: 220 success
     * @return bool
     */
    public function SMTPconnect()
    {
        // check if already connected
        if ($this->smtpStream) {
            return false;
        }
        // check for host
        if (isset($this->smtpHost) && $this->smtpHost != "") {
            $host_name = $this->smtpHost;
            $server_arr = [$this->smtpHost];
        } else {
            $host_name = $this->smtpDomain;
            $server_arr = [$this->smtpDomain];
        }
        // check for port
        if (isset($this->smtpPort) && $this->smtpPort != "") {
            $srv_ports = [$this->smtpPort];
        } else {
            $srv_ports = [587, 25, 2525];
        }
        // connect to the smtp server
        if (function_exists('stream_socket_client')) {
            $connect_options = $this->smtpOptions;
            $create_options = (!empty($connect_options)) ? stream_context_create($connect_options) : null;
        }
        foreach ($server_arr as $host) {
            if (!isset($code) || $code != '220') {
                foreach ($srv_ports as $port) {
                    if (function_exists('stream_socket_client')) {
                        $this->smtpStream = @stream_socket_client($host . ':' . $port, $errno, $errstr, PHPMailerPro::TIMEVAL, STREAM_CLIENT_CONNECT, $create_options);
                    } else {
                        $this->smtpStream = @fsockopen($host, $port, $errno, $errstr, PHPMailerPro::TIMEVAL);
                    }
                    if (!$this->smtpStream) {
                        continue;
                    }
                    $code = self::SMTPgetResponse(['220'], 'CONNECT (' . $host . ':' . $port . ')');
                    if ($code == '220') {
                        $this->smtpHost = $host;
                        break;
                    }
                }
            } else {
                break;
            }
        }
        // set the time out
        stream_set_timeout($this->smtpStream, PHPMailerPro::TIMEVAL);
        // send EHLO command
        fwrite($this->smtpStream, 'EHLO ' . $this->smtpHost . self::CRLF);
        self::SMTPgetResponse(['250'], 'EHLO');
        if (!self::SMTPisConnected()) {
            exit(__LINE__ . ' ' . PHPMailerPro::FAILMK . $this->language['not_connected'] . '<br>' . self::CRLF);
        }

        // send STARTTLS command
        fwrite($this->smtpStream, 'STARTTLS' . self::CRLF);
        self::SMTPgetResponse(['220'], 'STARTTLS');
        // initiate secure tls encryption
        $crypto_method = STREAM_CRYPTO_METHOD_TLS_CLIENT;
        if (defined('STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT')) {
            $crypto_method = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
        } elseif (defined('STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT')) {
            $crypto_method = STREAM_CRYPTO_METHOD_TLSv1_1_CLIENT;
        } elseif (defined('STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT')) {
            $crypto_method = STREAM_CRYPTO_METHOD_TLSv1_0_CLIENT;
        }
        stream_socket_enable_crypto($this->smtpStream, true, $crypto_method);

        // resend EHLO after tls negotiation
        fwrite($this->smtpStream, 'EHLO ' . $this->smtpHost . self::CRLF);
        self::SMTPgetResponse(['250'], 'EHLO');
        if (!self::SMTPisConnected()) {
            exit(__LINE__ . ' ' . PHPMailerPro::FAILMK . $this->language['not_connected'] . '<br>' . self::CRLF);
        }
        if ((isset($this->smtpUsername) && $this->smtpUsername != '') && (isset($this->smtpUsername) && $this->smtpUsername != '')) {
            // Authenticate
            fwrite($this->smtpStream, 'AUTH LOGIN' . self::CRLF);
            self::SMTPgetResponse(['334'], 'AUTH LOGIN');
            // Send encoded username
            fwrite($this->smtpStream, base64_encode($this->smtpUsername) . self::CRLF);
            self::SMTPgetResponse(['334'], 'USER');
            // Send encoded password
            fputs($this->smtpStream, base64_encode($this->smtpPassword) . self::CRLF);
            self::SMTPgetResponse(['235'], 'PASS');
        }
        if (!self::SMTPisConnected()) {
            exit(__LINE__ . ' ' . PHPMailerPro::FAILMK . $this->language['not_connected'] . '<br>' . self::CRLF);
        }
        fwrite($this->smtpStream, "MAIL FROM: <" . $this->senderEmail . ">" . (($this->smtpUseVerp) ? "XVERP" : "") . self::CRLF);
        self::SMTPgetResponse(['250'], 'MAIL FROM');
        if (!self::SMTPisConnected()) {
            exit(__LINE__ . ' ' . PHPMailerPro::FAILMK . $this->language['not_connected'] . '<br>' . self::CRLF);
        }
        return true;
    }

    /**
     * Sends header and message to SMTP Server
     * return code: 250 success (possible 251, have to allow for this)
     * @return bool
     */
    public function SMTPdata($hdr, $msg)
    {
        if (!self::SMTPisConnected()) {
            exit(__LINE__ . ' ' . PHPMailerPro::FAILMK . $this->language['not_connected'] . '<br>' . self::CRLF);
        }
        // initiate DATA stream
        fwrite($this->smtpStream, "DATA" . self::CRLF);
        self::SMTPgetResponse(['354'], 'DATA');
        // send the header
        fwrite($this->smtpStream, $hdr . self::CRLF);
        // send the message
        fwrite($this->smtpStream, $msg . self::CRLF);
        // end DATA stream
        fwrite($this->smtpStream, '.' . self::CRLF);
        self::SMTPgetResponse(['250'], 'END');
        return true;
    }

    /**
     * Get response code returned by SMTP server
     * @param array $expected_code
     * @param string $command
     * @return string
     */
    private function SMTPgetResponse($expected_code, $command = '')
    {
        $line = "";
        $cmd = ($command != '') ? '' . $command . ' - ' : '';
        while (substr($line, 3, 1) != ' ') {
            $line = stream_get_line($this->smtpStream, 2048, "\n");
            $code = substr($line, 0, 3);
            $data = trim(substr($line, 4));
            if (!$line) {
                exit(PHPMailerPro::FAILMK . $cmd . ' ' . $this->language['server_response'] . '<br>' . self::CRLF);
            }
        }
        if (!in_array($code, $expected_code)) {
            exit(PHPMailerPro::FAILMK . $cmd . ' ' . $this->language['email_send'] . ' "' . $line . '"<br>' . self::CRLF);
        }
        if ($this->smtpDebug > 0) {
            $debug_text = ($this->smtpDebug > 0) ? ' (' . $data . ')' : '';
            // put response(s) into feedback array
            switch ($command) {
                case strstr($command, 'CONNECT'):
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['connected'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case strstr($command, 'AUTH'):
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['authenticated'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'DATA':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['transfer_started'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'EHLO':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['connection_replies'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'END':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['transfer_accepted'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'HELO':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['connection_replies'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'MAIL FROM':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['MAIL_FROM'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'PASS':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['password_accepted'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'QUIT':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['transfer_completed'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'RCPT TO':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['RCPT_TO'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'RSET':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['keep_alive_accepted'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'STARTTLS':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['STARTTLS'] . $debug_text . '<br>' . self::CRLF;
                    break;
                case 'USER':
                    $this->smtpFeedback[] = PHPMailerPro::PASSMK . $this->language['username_accepted'] . $debug_text . '<br>' . self::CRLF;
                    break;
                default:
                    $this->smtpFeedback[] = $this->language['unknown'] . $command . $debug_text . '<br>' . self::CRLF;
            }
        }
        return substr($line, 0, 3);
    }

    /**
     * Returns true if connected to a server otherwise false
     * @access public
     * @return bool
     */
    public function SMTPisConnected()
    {
        if (!empty($this->smtpStream)) {
            $status = socket_get_status($this->smtpStream);
            if ($status["eof"]) {
                fclose($this->smtpStream);
                $this->smtpStream = 0;
                exit(PHPMailerPro::FAILMK . $this->language['connection_error'] . '<br>' . self::CRLF);
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * Sends QUIT to SMTP Server then closes the stream
     * return code: 221 success
     * @return bool
     */
    public function SMTPquit()
    {
        if (!self::SMTPisConnected()) {
            exit(__LINE__ . ' ' . PHPMailerPro::FAILMK . $this->language['not_connected'] . '<br>' . self::CRLF);
        }
        // send QUIT to the SMTP server
        fwrite($this->smtpStream, "quit" . self::CRLF);
        self::SMTPgetResponse(['221'], 'QUIT');
        // close connection and reset smtpStream
        if (!empty($this->smtpStream)) {
            fclose($this->smtpStream);
            $this->smtpStream = 0;
        }
        return true;
    }

    /**
     * Sends smtp command RCPT TO
     * Returns true if recipient (email) accepted (false if not)
     * return code: 250 success (possible 251, have to allow for this)
     * @return bool
     */
    public function SMTPrecipient($param)
    {
        //error_log("INVOKED: $param");
        /*$addresses = [];
        if (is_string($param) && strpos($param, ',') !== false) {
            $addresses = explode(',', $param);
        } elseif (is_string($param)) {
            $addresses[] = $param;
        }
        $emails = [];
        foreach ($addresses as $key => $val) {
            error_log("MAILKEY: $key, MAILVAL: $val");
            $emails[] = self::EmailExtractEmail($val);
        }
        foreach ($emails as $email) {
            fwrite($this->smtpStream, "RCPT TO: " . $email . self::CRLF);
            $code = self::SMTPgetResponse(['250', '251'], 'RCPT TO');
        }*/
        fwrite($this->smtpStream, "RCPT TO: <" . $param . ">" . self::CRLF);
        $code = self::SMTPgetResponse(['250', '251'], 'RCPT TO');
    }

    /* Send RSET
     * (aborts any transport in progress and keeps connection alive)
     * Implements RFC 821: RSET <CRLF>
     * return code 250 success
     * @return bool
     */
    public function SMTPreset()
    {
        if (!self::SMTPisConnected()) {
            exit(__LINE__ . ' ' . PHPMailerPro::FAILMK . $this->language['keep_alive_error'] . '<br>' . self::CRLF);
        }
        fwrite($this->smtpStream, "RSET" . self::CRLF);
        $code = self::SMTPgetResponse(['250'], 'RSET');
        return true;
    }

    /* END - SMTP METHODS ************/
}

/* PHPMailer Pro part of PHP Exception error handling
 * (note, namespace makes Exception unique)
 */
class Exception extends \Exception
{
    public function errorMessage()
    {
        $msg = str_ireplace(["<br>", "<br/>", "<br />"], "\n", $this->getMessage());
        $errorMsg = "<style>.bmh-alert {width:600px;max-width:600px;border-radius:5px;border-style:solid;border-width:1px;font-family:sans-serif;font-size:22px;font-weight:bold;margin:40px 20px;padding:12px 16px;width:80%;}.bmh-alert.bmh-danger {background-color:rgba(248, 215, 218, 1);border-color:rgba(220, 53, 69, 1);color:rgba(114, 28, 36,1);}.bmh-alert.bmh-info {background-color:rgba(217, 237, 247, 1);color:rgba(49, 112, 143, 1);border-color:rgba(126, 182, 193, 1);}</style>";
        $errorMsg .= "<div class=\"bmh-alert bmh-danger\" role=\"alert\">";
        $errorMsg .= htmlentities($msg);
        $errorMsg .= "</div>\n";
        return $errorMsg;
    }
    public function errorMessageRaw()
    {
        $msg = $this->getMessage();
        if ($this->getCode() == PHPMailerPro::ERR_CRITICAL) {
            return $msg;
            exit();
        }
        return $msg;
    }
}
