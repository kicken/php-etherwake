# PHP Etherwake
Generate and send a magic Wake-on-lan packet.

## Example

    $targetMac = "b4:2e:99:ed:3e:20";

    //Send magic packet to global broadcast address 255.255.255.255 on UDP port 9
    \Kicken\Etherwake\etherwake($targetMac);

    //Send magic packet to network broadcast address 192.168.10.255 on UDP port 9
    \Kicken\Etherwake\etherwake($targetMac, '192.168.10.255');

    //Send magic packet to network broadcast address 192.168.10.255 on UDP port 7
    \Kicken\Etherwake\etherwake($targetMac, '192.168.10.255', 7);

