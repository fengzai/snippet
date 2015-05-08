/*=============================================================================
#     FileName: UpdateHosts.java
#         Desc: update hosts
#       Author: Cloud Tao
#        Email: cloud@txthinking.com
#      Version: 0.0.1
#   LastChange: 2012-10-15 16:06:13
#      History:
=============================================================================*/
import java.net.URL;
import java.net.URLConnection;

import java.io.InputStream;
import java.io.InputStreamReader;
import java.io.BufferedReader;

import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;

import java.lang.String;
import java.io.IOException;

public class UpdateHosts{
	public String hostsFile = "/etc/hosts";
	public String hostsUrl = "http://services.txthinking.com/tx-hosts/hosts";
	public String findString = "#TX-HOSTS";

	public static void main(String[] args) throws IOException{
		UpdateHosts uh = new UpdateHosts();
		String remoteHosts = uh.getRemoteHosts();
		//System.exit(0);
		String yourHosts = uh.getYourHosts();
		String hosts = yourHosts + uh.findString + "\n" + remoteHosts;
		uh.update(hosts);
		System.out.println("Success");
	}

	/**
	 */
	public UpdateHosts(){
	}

	/**
	 * get remote hosts
	 */
	public String getRemoteHosts() throws IOException{
		URL url = new URL(this.hostsUrl);
		URLConnection uc = url.openConnection();

		InputStream is = uc.getInputStream();
		InputStreamReader isr = new InputStreamReader(is);
		BufferedReader br = new BufferedReader(isr);

		String remoteHosts = "";
		String line;
		for(;;){
			line = br.readLine();
			if(line == null){
				break;
			}
			remoteHosts += line + "\n";
		}
		br.close();
		return remoteHosts;
	}

	/**
	 * get yourself hosts
	 */
	public String getYourHosts() throws IOException{
		File f = new File(this.hostsFile);
		FileReader fr = new FileReader(f);
		BufferedReader br = new BufferedReader(fr);

		String yourHosts = "";
		String line;
		for(;;){
			line = br.readLine();
			if(line == null || line.compareTo(this.findString) == 0){
				break;
			}
			yourHosts += line + "\n";
		}
		br.close();
		return yourHosts;
	}

	/**
	 * update your hosts
	 */
	public void update(String hosts) throws IOException{
		File f1 = new File(this.hostsFile);
		File f2 = new File(this.hostsFile + "-TX-HOSTS-BK");
		f1.renameTo(f2);
		f1.createNewFile();
		FileWriter fw = new FileWriter(f1);
		fw.write(hosts);
		fw.close();
	}
}

