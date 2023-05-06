<?php

namespace Kicken\Etherwake;

use InvalidArgumentException;
use RuntimeException;

function etherwake(mixed $macAddress, string $sendTo = '255.255.255.255', int $port = 9) : bool{
    if (!filter_var($sendTo, FILTER_VALIDATE_IP)){
        throw new InvalidArgumentException('Invalid send to address.');
    }

    if ($port < 1 || $port > 65535){
        throw new InvalidArgumentException('Invalid port.');
    }

    $context = stream_context_create(['socket' => ['so_broadcast' => true]]);
    $socket = stream_socket_server('udp://0.0.0.0:0', $errNo, $errStr, STREAM_SERVER_BIND, $context);
    if (!$socket){
        throw new RuntimeException(sprintf('Unable to create local socket [%d] %s', $errNo, $errStr));
    }

    $destination = sprintf('%s:%d', $sendTo, $port);
    $magic = generateMagicPacket($macAddress);
    $ret = stream_socket_sendto($socket, $magic, 0, $destination);
    fclose($socket);

    return $ret === strlen($magic);
}

function generateMagicPacket(string|int|array $mac) : string{
    $macIntegers = [];
    if (is_int($mac)){
        for ($byte = 0; $byte < 6; $byte++){
            $offset = 8 * (5 - $byte);
            $mask = 0xFF << $offset;
            $macIntegers[$byte] = ($mac & $mask) >> $offset;
        }
    } else if (is_string($mac) || is_array($mac)){
        if (is_string($mac)){
            //Remove common separators
            $mac = str_replace([':', '-'], '', $mac);
            $mac = str_split($mac, 2);
        }

        $macIntegers = array_map(function(string|int $byte){
            if (is_string($byte)){
                if (!preg_match('/^[0-9a-f]{2}$/i', $byte)){
                    throw new InvalidArgumentException('Invalid MAC byte.');
                }
                $byte = base_convert($byte, 16, 10);
            }

            return $byte;
        }, $mac);
    }

    if (count($macIntegers) !== 6){
        throw new InvalidArgumentException('Invalid MAC');
    }

    $macBinary = array_map(function(int $value){
        if ($value < 0 || $value > 255){
            throw new InvalidArgumentException('MAC value out of range');
        }

        return chr($value);
    }, $macIntegers);

    $packet = str_repeat(chr(255), 6);
    $packet .= str_repeat(implode('', $macBinary), 16);

    return $packet;
}
