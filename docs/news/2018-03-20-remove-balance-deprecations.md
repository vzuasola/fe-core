# Removal of Deprecated Balance Calls

The following method calls on the balance fetcher is now removed (the CMS API Layer counterparts
are removed as well)

* **GetBalanceByProduct**
* **GetBonusBalanceByProduct**
* **GetReservedBalanceByProduct**
* **GetNonWithdrawableBalanceByProduct**

These are single product return methods wherein you pass the product ID

```php
$id = 2;
$result = $this->balanceFetcher->getBalanceByProduct($id);
```

They are replaced by more robust and performant methods which expects an array of
product IDs

* **GetBalanceByProductIds**
* **GetBonusBalanceByProductIds**
* **GetReservedBalanceByProductIds**
* **GetNonWithdrawableBalanceByProductIds**

```php
$ids = [2, 3, 5];
$result = $this->balanceFetcher->getBalanceByProductIds($ids);

// result is an assoc array of format [2 => 1000, 3 => 1000, 5 => 1000]
```
