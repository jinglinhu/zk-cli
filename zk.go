/*

 基于zookeeper的分布式脚本执行-客户端监听程序-go版

 初尝go,请多指正 hujinglin-ps
*/
package main

import (
	"flag"
	"fmt"
	"time"

	"github.com/samuel/go-zookeeper/zk"
)

func main() {

	//获取模块参数
	module := flag.String("m", "", "module name (zk_path only path)")

	flag.Parse()

	if *module == "" {
		fmt.Println("Module name not null,please carry the parameter like '-m＝xxx' or '-m xxx'")
		return
	}

	//path字符串拼接
	path := "/adep/" + *module + "/cli"
	//fmt.Println(path)

	//创建zookeeper连接
	c, _, err := zk.Connect([]string{"xx.xxx.xxx.xx:2181", "xx.xxx.xxx.xx:2181", "xx.xxx.xxx.xx:2181"}, time.Second*5)
	if err != nil {
		fmt.Println(err)
		return
	}

	//判断是否存在
	isExist, _, err := c.Exists(path)
	if err != nil {
		fmt.Println(err)
		return
	}
	if isExist == false {
		fmt.Println("The listen module does not exist,please connect google.translation('平台系统组') to create first")
		return
	}

	//开始监听
	d, _, ch, err := c.GetW(path)
	if err != nil {
		fmt.Println(err)
		return
	}

	fmt.Println(string(d))

	// e := <-ch
	// fmt.Printf("%+v\n", e)
	// select {}

}
