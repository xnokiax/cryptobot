select a.*
 from (
select 
id
,chainId
,baseToken_address
,baseToken_name
,baseToken_symbol
,pairCreatedAtTime
,fdv
,txns_m5_buys
-- ,(select avg(txns_m5_buys) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_txns_m5_buys
,txns_m5_sells
-- ,(select avg(txns_m5_sells) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_txns_m5_sells
,txns_h1_buys
-- ,(select avg(txns_h1_buys) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_txns_h1_buys
,txns_h1_sells
-- ,(select avg(txns_h1_sells) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_txns_h1_sells
,volume_m5
-- ,(select avg(volume_m5) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_volume_m5
,volume_h1
-- ,(select avg(volume_h1) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_volume_h1
,priceChange_m5
-- ,(select avg(priceChange_m5) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_priceChange_m5
,priceChange_h1
-- ,(select avg(priceChange_h1) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_priceChange_h1
,liquidity_usd
-- ,(select avg(liquidity_usd) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_liquidity_usd
,marketCap
-- ,(select avg(marketCap) from tokens_dexscreener where pairCreatedAtTime >= now() - interval 3 day) as avg_marketCap
,(select max(id) from tokens_dexscreener where baseToken_address = t.baseToken_address) as max_id
from tokens_dexscreener t

where pairCreatedAtTime >= now() - interval 14 hour
and created_at > now() - interval 1 hour
-- and txns_m5_buys > avg_txns_m5_buys
) a
left outer join tokens_solscanner ts on a.baseToken_address = ts.baseToken_address

where 1


and a.liquidity_usd > 15000
and a.fdv > 100000
and txns_h1_buys > 109
and txns_m5_buys > 19 -- i added this
and a.id = max_id
and txns_h1_buys > (txns_h1_sells*1.2)


order by id desc
;