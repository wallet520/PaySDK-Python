# !/usr/bin/env python3
# -*- coding: utf-8 -*-
# @Time : 2021/11/2 9:53
# @File : api_sdk.py
# @Software: PyCharm

import uuid
import time
import base64
import hmac
from hashlib import sha1
import json
import requests

# hmac function
def hash_hmac(code, key, sha1):
    hmac_code = hmac.new(key.encode(), code.encode(), sha1).digest()
    return base64.b64encode(hmac_code).decode()

# function to generate signature
def genSign(params=None):
    map = {'access_key' : access_key, 'timestamp':str(t), 'nonce':nonce}
    if params:
        map.update(params)
    map_str =''
    keys = sorted(map.keys())
    for key in keys:
        map_str = map_str + str(key) + '=' + str(map[key]) + '&'
    map_str = map_str[:-1]
    sign = hash_hmac(map_str, secret_key, sha1)
    return sign

def getMerchantAccountList(url='/api/v1/cashier/accounts', headers=None):
    r = requests.get(host+url, headers=headers)
    rlt = json.loads(r.text)
    return rlt

def newInvoice(url='/api/v1/cashier/product', headers=None, newInvoiceJson=None):
    r = requests.post(host+url, headers=headers, json=newInvoiceJson)
    rlt = json.loads(r.text)
    return rlt

def getReceiptList(url='/api/v1/cashier/products', headers = None, params=None):
    r = requests.get(host+url, headers=headers, params=params)
    rlt = json.loads(r.text)
    return rlt

def getStatementList(url='/api/v1/cashier/books', headers = None, params=None):
    r = requests.get(host+url, headers=headers, params=params)
    rlt = json.loads(r.text)
    return rlt

def newTopUp(url='/api/v1/cashier/add/recharge', headers=None, newTopUp=None):
    r = requests.post(host+url, headers=headers, json=newTopUp)
    rlt = json.loads(r.text)
    return rlt

def newTransfer(url='/api/v1/cashier/add/recharge', headers=None, newTransfer=None):
    r = requests.post(host+url, headers=headers, json=newTransfer)
    rlt = json.loads(r.text)
    return rlt

if __name__ == '__main__':
    t = time.time()
    t = int(round(t * 1000))
    access_key = '' # input api key
    secret_key = "" # input secret key
    nonce = uuid.uuid1()
    # example input for getReceiptList
    params3 = {"productId":3256489865456, "externalOrderId": "564654651", "storeName":"test Store"}
    # example input for getStatementList
    params4 = {"page":1, "size": 20,"accountBookType":0,"accountBookSourceType":0}
    # Generate signature for header. params=None if not necessary
    sign = genSign(params=params4)
    headers = {'access_key': access_key, 'timestamp': str(t), 'nonce': str(nonce), 'sign': sign}
    # example input for newInvoice
    newInvoiceJson = {
            "externalOrderId": "54566-abc5748941320",
            "productsAmount": 0.0,
            "currencyType": "USD",
            "goodsList": [
            {
            "goodsName": "iphone13",
            "goodsPrice": 100,
            "goodsSize": 1
            },
            {
            "goodsName": "iphone45",
            "goodsPrice": 1000,
            "goodsSize": 100
            }
            ],
            "remark": "remark",
            "storeName": "test store",
            "tokenType": 901,
            "assetType": 102
    }
    # example input for newTopUp
    newTopUpJson = {
        "amount": 100.0,
        "assetType": 102,
        "externalOrderId": "1231fdsfd",
        "payAddress": "0xfdgfdgdgd",
        "remark": "test",
        "tokenType": 901,
        "currencyType": "USD"
        }
    # example input for newTransfer
    newTransferJson = {
        "addressTo": "0xdfsgdfgdgdfdg",
        "amount": 100.0,
        "assetType": 102,
        "externalOrderId": "abc123ads",
        "tokenType": 901
        }

    host = 'Please contact technical support' # Production Server
    # a= getMerchantAccountList(headers=headers)
    # a= newInvoice(headers=headers, newInvoiceJson=newInvoiceJson)
    # a = getReceiptList(headers=headers, params=params3)
    a = getStatementList(headers=headers, params=params4)
    # a = newTopUp(headers=headers, newTopUp=newTopUpJson)
    # a = newTransfer(headers=headers, newTransfer=newTransferJson)
    print(a)