/*

 基于zookeeper的分布式脚本执行-客户端监听程序-go版

*/
package main

import (
	"bytes"
	"errors"
	"flag"
	"fmt"
	"net"
	"os/exec"
	"strconv"
	"strings"
	"time"

	"encoding/json"
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

	//创建zookeeper连接
	c, _, err := zk.Connect([]string{"x.x.x.x:2181", "x.x.x.x:2181", "x.x.x.x:2181"}, time.Second*1)
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

	//获取本机ip
	addrs, err := net.InterfaceAddrs()
	if err != nil {
		fmt.Println(err)
		return
	}
	var ip string
	for _, address := range addrs {
		// 检查ip地址判断是否回环地址
		if ipnet, ok := address.(*net.IPNet); ok && !ipnet.IP.IsLoopback() {
			if ipnet.IP.To4() != nil {
				ip = ipnet.IP.String()
			}

		}
	}

	fmt.Println(time.Now().Format("2006-01-02 15:04:05") + " Begin listening,module:" + *module)
	//开始监听
	for {
		_, _, ch, err := c.GetW(path)
		if err != nil {
			fmt.Println(err)
			return
		}

		//获取到监听数据
		e := <-ch
		if e.Type.String() == "EventNodeDataChanged" {

			//从zk中拿到更新的执行命令
			cmd, _, err := c.Get(path)
			if err != nil {
				fmt.Println(err)
				return
			}

			result := map[string]string{}
			result["path"] = path
			result["cmd"] = string(cmd)
			result["ip"] = ip
			result["time"] = time.Now().Format("2006-01-02 15:04:05")

			//处理命令字符串
			cmd_str := strings.Split(string(cmd), "--timeout=")
			timeout := 1000
			if len(cmd_str) == 2 {
				t, _ := strconv.Atoi(cmd_str[1])
				timeout = t * 1000
			}
			//执行命令
			cmd_res, err := Exec(cmd_str[0], timeout)

			if err != nil && err.Error() == "timeout" {
				cmd_res = err.Error()
			} else {
				cmd_res = strings.Replace(cmd_res, "\n", "<br>", -1)

				if cmd_res == "" {
					cmd_res = "no data"
				}
			}
			result["data"] = cmd_res
			result_json, _ := json.Marshal(result)
			fmt.Println(string(result_json))

			//将处理结果写入zookeeper
			res_path := "/adep/" + *module + "/res/" + ip

			isExist, _, err := c.Exists(res_path)
			if err != nil {
				fmt.Println(err)
				return
			}
			if isExist == false {
				if _, err := c.Create(res_path, []byte(""), int32(0), zk.WorldACL(zk.PermAll)); err != nil {
					fmt.Println(err)
					return
				}
			}
			if _, err := c.Set(res_path, result_json, -1); err != nil {
				fmt.Println(err)
				return
			}
		}
	}
}

func Exec(c string, timeout int) (output string, err error) {
	var stdout, stderr bytes.Buffer
	done := make(chan error)

	cmd := exec.Command("/bin/sh", "-c", c)
	cmd.Stderr = &stderr
	cmd.Stdout = &stdout

	go func() {
		done <- cmd.Run()
	}()

	select {
	case err = <-done:
	case <-time.After(time.Duration(timeout) * time.Millisecond):
		err = errors.New("timeout")
		cmd.Process.Kill()
	}

	if err != nil {
		return stderr.String(), err
	}

	return stdout.String(), err
}
