# RandomBox : RB
The RandomBox is give to item, money and effect!
Now, Open the RandomBox. RB's prize is random from you.
You can setup to RB event ended day and prizes.
[ Project Created by Laeng ]

##TODO
- [x] Daily free offer to RB
- [ ] RB Auto Selling System.

##Developer Guide
###How to access the 'RB'?
You can access to Job by using `RandBox::getInstance()` <br/>
Example: `RandBox::getInstance()->giveRB($player, $unit);`
>Notice: You must add `use Laeng/RandomBox`.

###RB API List
| Function | Return | Description | Required |
| ----- | :------: | ----------- | :---------: |
| giveRB($player, $unit) | | Provide RB to `$player->getName()` | |
| openRB($player) | | Open `$player->getName()`'s RB | |
| &nbsp; | &nbsp; | &nbsp; | &nbsp; |
| addMoney($player, $amount) | | Add `$player->getName()`'s Money | Money Plugin |
| takeMoney($player, $amount) | | Take `$player->getName()`'s Money | Money Plugin |
>  Money Plugin: [EconomyS](#), [MassiveEconomy](#)

##License
```
RandomBox for PocketMine-MP
Copyright (c) 2015 Laeng

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
```
