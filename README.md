# EBS

As part of our responsibility towards our community, and in support of e-government efforts, everyone can now use e-payment in all government and private applications and services.


## Providers

### [Cashaman](https://cashaman.net/)

 #### Installation 
 Via composer
  ```bash
  composer require deepai-sd/ebs
  ```
  
 ### Usage
  ```php
   $cashaman = new Ebs\Gateways\Cashaman(
       "USERNAME",
       "PASSWORD"
   );
   ```
 ### Methods
   1. Transfer Money:
       Used to transfer money from card to other.<br>
       
      ```php
        $cashaman->transfer($from, $to, $exp, $ipin, $amount);
        ```   
 
 ### Requirements
   1. API Credential (Username,Password).  
   

## Sponsors 
1. [Zoul Vision Infrastructure Co. Ltd](https://zoulvision.com) (**Golden**):<br>
   ![Zoul Vision Infrastructure Co. Ltd](https://zoulvision.com/images/zoulvision.png)
